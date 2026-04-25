<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryReportController extends Controller
{
    public function index()
    {
        // Get inventory data
        $inventoryData = $this->getInventoryReportsData();

        return view('reports.inventory.index', compact('inventoryData'));
    }

    private function getInventoryReportsData()
    {
        // Stock Levels
        $stockLevels = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', true)
            ->select(
                'products.name',
                'categories.name as category_name',
                'products.quantity_in_stock',
                'products.reorder_level',
                'products.latest_unit_cost',
                DB::raw('(products.quantity_in_stock * COALESCE(products.latest_unit_cost, 0)) as stock_value')
            )
            ->orderBy('products.quantity_in_stock')
            ->paginate(10, ['*'], 'stock_levels_page');

        // Low Stock Alerts
        $lowStockAlerts = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', true)
            ->where(function($query) {
                $query->where('products.quantity_in_stock', '<=', DB::raw('products.reorder_level'))
                      ->orWhere('products.quantity_in_stock', 0);
            })
            ->select(
                'products.id',
                'products.name',
                'categories.name as category_name',
                'products.quantity_in_stock',
                'products.reorder_level',
                'products.latest_unit_cost'
            )
            ->orderBy('products.quantity_in_stock')
            ->paginate(10, ['*'], 'low_stock_page');

        // Stock Adjustments (latest 20)
        $adjustments = DB::table('stock_adjustments')
            ->join('stock_adjustment_items', 'stock_adjustment_items.stock_adjustment_id', '=', 'stock_adjustments.id')
            ->join('products', 'stock_adjustment_items.product_id', '=', 'products.id')
            ->join('users', 'stock_adjustments.processed_by_user_id', '=', 'users.id')
            ->select(
                'products.name as product_name',
                'stock_adjustments.adjustment_date',
                'stock_adjustments.adjustment_type',
                'stock_adjustments.reason_notes',
                'stock_adjustment_items.quantity_change',
                'stock_adjustment_items.unit_cost_at_adjustment',
                DB::raw("CONCAT(users.f_name, ' ', COALESCE(users.m_name, ''), ' ', users.l_name) AS processed_by")
            )
            ->orderBy('stock_adjustments.adjustment_date', 'desc')
            ->limit(20)
            ->get();
    
        // Stock Movement
        $stockMovement = DB::table('stock_in_items')
            ->join('products', 'stock_in_items.product_id', '=', 'products.id')
            ->join('stock_ins', 'stock_in_items.stock_in_id', '=', 'stock_ins.id')
            ->select(
                'products.name',
                'stock_ins.stock_in_date',
                'stock_in_items.quantity_received',
                'stock_in_items.actual_unit_cost',
                DB::raw('(stock_in_items.quantity_received * stock_in_items.actual_unit_cost) as total_cost')
            )
            ->orderBy('stock_ins.stock_in_date', 'desc')
            ->limit(10)
            ->get();

        // Returns Stock Impact
        $returns = DB::table('product_returns')
            ->join('return_items', 'return_items.product_return_id', '=', 'product_returns.id')
            ->join('products', 'return_items.product_id', '=', 'products.id')
            ->join('users', 'product_returns.user_id', '=', 'users.id')
            ->select(
                'products.name as product_name',
                'product_returns.created_at',
                'product_returns.return_reason',
                'product_returns.notes',
                'return_items.quantity_returned',
                'return_items.inventory_adjusted',
                'return_items.refunded_price_per_unit',
                DB::raw("CONCAT(users.f_name, ' ', COALESCE(users.m_name,''), ' ', users.l_name) AS processed_by")
            )
            ->orderBy('product_returns.created_at', 'desc')
            ->limit(10)
            ->get();

        // Valuation Report
        $valuationReport = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', true)
            ->select(
                'categories.name as category_name',
                DB::raw('COUNT(*) as product_count'),
                DB::raw('SUM(products.quantity_in_stock) as total_quantity'),
                DB::raw('SUM(products.quantity_in_stock * COALESCE(products.latest_unit_cost, 0)) as total_value')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_value')
            ->get();

        // Summary Statistics
        $summaryStats = DB::table('products')
            ->where('products.is_active', true)
            ->select(
                DB::raw('COUNT(*) as total_products'),
                DB::raw('SUM(quantity_in_stock) as total_quantity'),
                DB::raw('SUM(quantity_in_stock * COALESCE(latest_unit_cost, 0)) as total_inventory_value'),
                DB::raw('COUNT(CASE WHEN quantity_in_stock <= reorder_level OR quantity_in_stock = 0 THEN 1 END) as low_stock_count'),
                DB::raw('COUNT(CASE WHEN quantity_in_stock = 0 THEN 1 END) as out_of_stock_count')
            )
            ->first();

            $inventoryTurnover = $this->calculateInventoryTurnover();

            // Product Popularity / Best Sellers
            $bestSellers = DB::table('sale_items')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.sale_date', '>=', now()->subMonths(3)) // Last 3 months
                ->select(
                    'products.name',
                    'products.sku',
                    DB::raw('SUM(sale_items.quantity_sold) as total_quantity_sold'),
                    DB::raw('SUM(sale_items.unit_price * sale_items.quantity_sold) as total_revenue'),
                    DB::raw('AVG(sale_items.unit_price) as avg_selling_price')
                )
                ->groupBy('products.id', 'products.name', 'products.sku')
                ->orderByDesc('total_quantity_sold')
                ->limit(10)
                ->get();
        
            // Dead Stock / Excess Inventory
            $deadStock = DB::table('products')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
                ->leftJoin('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('products.is_active', true)
                ->where('products.quantity_in_stock', '>', 0)
                ->select(
                    'products.name',
                    'categories.name as category_name',
                    'products.quantity_in_stock',
                    'products.latest_unit_cost',
                    DB::raw('(products.quantity_in_stock * COALESCE(products.latest_unit_cost, 0)) as stock_value'),
                    DB::raw('MAX(sales.sale_date) as last_sale_date'),
                    DB::raw('COUNT(sale_items.id) as sales_count'),
                    DB::raw('COALESCE(SUM(sale_items.quantity_sold), 0) as total_sold')
                )
                ->groupBy('products.id', 'products.name', 'categories.name', 'products.quantity_in_stock', 'products.latest_unit_cost')
                ->havingRaw('last_sale_date IS NULL OR last_sale_date < ?', [now()->subMonths(6)])
                ->orderByDesc('stock_value')
                ->paginate(10, ['*'], 'dead_stock_page'); 

        return [
            'stockLevels' => $stockLevels,
            'lowStockAlerts' => $lowStockAlerts,
            'stockMovement' => $stockMovement,
            'valuationReport' => $valuationReport,
            'summaryStats' => $summaryStats,
            'stockAdjustments' => $adjustments ,
            'returns' => $returns,
            'inventoryTurnover' => $inventoryTurnover,
            'bestSellers' => $bestSellers,
            'deadStock' => $deadStock,
        ];
    }

    private function calculateInventoryTurnover()
    {
        // Calculate for the last 12 months
        $startDate = now()->subYear();
        $endDate = now();

        // Cost of Goods Sold (COGS)
        $cogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(DB::raw('SUM(sale_items.quantity_sold * sale_items.unit_price) as total_cogs'))
            ->first();

        // Average Inventory Value (using monthly snapshots or current value)
        $averageInventory = DB::table('products')
            ->where('is_active', true)
            ->select(DB::raw('AVG(quantity_in_stock * COALESCE(latest_unit_cost, 0)) as avg_inventory_value'))
            ->first();

        $cogsValue = $cogs->total_cogs ?? 0;
        $avgInventoryValue = $averageInventory->avg_inventory_value ?? 1; // Avoid division by zero

        $turnoverRate = $avgInventoryValue > 0 ? $cogsValue / $avgInventoryValue : 0;

        return [
            'turnover_rate' => round($turnoverRate, 2),
            'cogs' => $cogsValue,
            'avg_inventory_value' => $avgInventoryValue,
            'period' => 'Last 12 Months'
        ];
    }

    public function exportLowStockCSV()
    {
        $products = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', true)
            ->where(function($query) {
                $query->where('products.quantity_in_stock', '<=', DB::raw('products.reorder_level'))
                      ->orWhere('products.quantity_in_stock', 0);
            })
            ->select(
                'products.name',
                'categories.name as category_name',
                'products.quantity_in_stock',
                'products.reorder_level',
                'products.latest_unit_cost'
            )
            ->orderBy('products.quantity_in_stock')
            ->get()
            ->map(function($item) {
                $item->status = $item->quantity_in_stock == 0 ? 'Out of Stock' : 'Low Stock';
                return $item;
            });

        return $this->exportToCSV($products, 'low-stock-alerts', [
            'Product', 'Category', 'Current Stock', 'Reorder Level', 'Unit Cost', 'Status'
        ]);
    }

    // Stock Movement CSV
    public function exportStockMovementCSV()
    {
        $movements = DB::table('stock_in_items')
            ->join('products', 'stock_in_items.product_id', '=', 'products.id')
            ->join('stock_ins', 'stock_in_items.stock_in_id', '=', 'stock_ins.id')
            ->select(
                'products.name',
                'stock_ins.stock_in_date',
                'stock_in_items.quantity_received',
                'stock_in_items.actual_unit_cost',
                DB::raw('(stock_in_items.quantity_received * stock_in_items.actual_unit_cost) as total_cost')
            )
            ->orderBy('stock_ins.stock_in_date', 'desc')
            ->get();

        return $this->exportToCSV($movements, 'stock-movement', [
            'Product', 'Date', 'Qty Received', 'Unit Cost', 'Total Cost'
        ]);
    }

    // Valuation Report CSV
    public function exportValuationCSV()
    {
        $valuation = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', true)
            ->select(
                'categories.name as category_name',
                DB::raw('COUNT(*) as product_count'),
                DB::raw('SUM(products.quantity_in_stock) as total_quantity'),
                DB::raw('SUM(products.quantity_in_stock * COALESCE(products.latest_unit_cost, 0)) as total_value')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_value')
            ->get();

        return $this->exportToCSV($valuation, 'inventory-valuation', [
            'Category', 'Products', 'Total Quantity', 'Total Value'
        ]);
    }

    // Stock Adjustments CSV
    public function exportAdjustmentsCSV()
    {
        $adjustments = DB::table('stock_adjustments')
            ->join('stock_adjustment_items', 'stock_adjustment_items.stock_adjustment_id', '=', 'stock_adjustments.id')
            ->join('products', 'stock_adjustment_items.product_id', '=', 'products.id')
            ->join('users', 'stock_adjustments.processed_by_user_id', '=', 'users.id')
            ->select(
                'products.name as product_name',
                'stock_adjustments.adjustment_date',
                'stock_adjustments.adjustment_type',
                'stock_adjustments.reason_notes as reason',
                'stock_adjustment_items.quantity_change',
                DB::raw("CONCAT(users.f_name, ' ', COALESCE(users.m_name, ''), ' ', users.l_name) AS processed_by")
            )
            ->orderBy('stock_adjustments.adjustment_date', 'desc')
            ->get();

        return $this->exportToCSV($adjustments, 'stock-adjustments', [
            'Date', 'Product', 'Change', 'Type', 'Reason', 'Processed By'
        ]);
    }

    // Returns CSV
    public function exportReturnsCSV()
    {
        $returns = DB::table('product_returns')
            ->join('return_items', 'return_items.product_return_id', '=', 'product_returns.id')
            ->join('products', 'return_items.product_id', '=', 'products.id')
            ->join('users', 'product_returns.user_id', '=', 'users.id')
            ->select(
                'products.name as product_name',
                'product_returns.created_at',
                'product_returns.return_reason',
                'return_items.quantity_returned',
                'return_items.inventory_adjusted',
                DB::raw("CONCAT(users.f_name, ' ', COALESCE(users.m_name,''), ' ', users.l_name) AS processed_by")
            )
            ->orderBy('product_returns.created_at', 'desc')
            ->get()
            ->map(function($item) {
                $item->stock_impact = $item->inventory_adjusted ? 'Added Back' : 'Not Added (Loss)';
                return $item;
            });

        return $this->exportToCSV($returns, 'returns-impact', [
            'Date', 'Product', 'Qty Returned', 'Stock Impact', 'Reason', 'Processed By'
        ]);
    }

    // Best Sellers CSV
    public function exportBestSellersCSV()
    {
        $bestSellers = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.sale_date', '>=', now()->subMonths(3))
            ->select(
                'products.name',
                'products.sku',
                DB::raw('SUM(sale_items.quantity_sold) as total_quantity_sold'),
                DB::raw('SUM(sale_items.unit_price * sale_items.quantity_sold) as total_revenue'),
                DB::raw('AVG(sale_items.unit_price) as avg_selling_price')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_quantity_sold')
            ->limit(50) // Increased limit for export
            ->get();

        return $this->exportToCSV($bestSellers, 'best-sellers', [
            'Product', 'SKU', 'Qty Sold', 'Revenue', 'Avg Price'
        ]);
    }

    // Dead Stock CSV
    public function exportDeadStockCSV()
    {
        $deadStock = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('sale_items', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('products.is_active', true)
            ->where('products.quantity_in_stock', '>', 0)
            ->select(
                'products.name',
                'categories.name as category_name',
                'products.quantity_in_stock',
                'products.latest_unit_cost',
                DB::raw('(products.quantity_in_stock * COALESCE(products.latest_unit_cost, 0)) as stock_value'),
                DB::raw('MAX(sales.sale_date) as last_sale_date'),
                DB::raw('COALESCE(SUM(sale_items.quantity_sold), 0) as total_sold')
            )
            ->groupBy('products.id', 'products.name', 'categories.name', 'products.quantity_in_stock', 'products.latest_unit_cost')
            ->havingRaw('last_sale_date IS NULL OR last_sale_date < ?', [now()->subMonths(6)])
            ->orderByDesc('stock_value')
            ->get();

        return $this->exportToCSV($deadStock, 'dead-stock', [
            'Product', 'Category', 'Current Stock', 'Stock Value', 'Last Sale', 'Total Sold'
        ]);
    }

    // Complete Stock Levels CSV
    public function exportStockLevelsCSV()
    {
        $stockLevels = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', true)
            ->select(
                'products.name',
                'categories.name as category_name',
                'products.quantity_in_stock',
                'products.reorder_level',
                'products.latest_unit_cost',
                DB::raw('(products.quantity_in_stock * COALESCE(products.latest_unit_cost, 0)) as stock_value')
            )
            ->orderBy('products.name')
            ->get()
            ->map(function($item) {
                if ($item->quantity_in_stock == 0) {
                    $item->status = 'Out of Stock';
                } elseif ($item->quantity_in_stock <= $item->reorder_level) {
                    $item->status = 'Low Stock';
                } else {
                    $item->status = 'In Stock';
                }
                return $item;
            });

        return $this->exportToCSV($stockLevels, 'complete-stock-levels', [
            'Product', 'Category', 'Current Stock', 'Reorder Level', 'Unit Cost', 'Stock Value', 'Status'
        ]);
    }

    private function exportToCSV($data, $filename, $headers)
    {
        $filename = $filename . '-' . now()->format('Y-m-d') . '.csv';
        
        $responseHeaders = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($data, $headers) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($file, $headers);
            
            foreach ($data as $row) {
                fputcsv($file, (array) $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $responseHeaders);
    }
}