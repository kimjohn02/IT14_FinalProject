<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request or default to this month
        $dateRange = $request->get('date_range', 'thismonth');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Set dates based on selection
        list($startDate, $endDate) = $this->calculateDateRange($dateRange, $startDate, $endDate);

        // Sales Reports Data
        $salesData = $this->getSalesReportsData($startDate, $endDate);
        $inventoryData = $this->getInventoryReportsData();
        $financialData = $this->getFinancialReportsData($startDate, $endDate);
        $returnsData = $this->getReturnsReportsData($startDate, $endDate);

        return view('reports.index', compact(
            'salesData',
            'inventoryData', 
            'financialData',
            'returnsData',
            'dateRange',
            'startDate',
            'endDate'
        ));
    }

    private function calculateDateRange($range, $customStart = null, $customEnd = null)
    {
        $today = Carbon::today();
        
        switch ($range) {
            case 'today':
                return [$today->copy()->startOfDay(), $today->copy()->endOfDay()];
            case 'yesterday':
                return [$today->copy()->subDay(), $today->copy()->subDay()];
            case 'thisweek':
                return [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()];
            case 'lastweek':
                return [$today->copy()->subWeek()->startOfWeek(), $today->copy()->subWeek()->endOfWeek()];
            case 'thismonth':
                return [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()];
            case 'lastmonth':
                return [$today->copy()->subMonth()->startOfMonth(), $today->copy()->subMonth()->endOfMonth()];
            case 'thisyear':
                return [$today->copy()->startOfYear(), $today->copy()->endOfYear()];
            case 'custom':
                return [
                    $customStart ? Carbon::parse($customStart) : $today->copy()->startOfMonth(), 
                    $customEnd ? Carbon::parse($customEnd) : $today->copy()->endOfMonth()
                ];
            default:
                return [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()];
        }
    }

    private function getSalesReportsData($startDate, $endDate)
    {
        // Net Sales by Date Range (Sales minus Returns)
        $salesByDate = DB::table('sales')
        ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
        ->leftJoin('product_returns', function ($join) {
            $join->on(DB::raw('DATE(product_returns.created_at)'), '=', DB::raw('DATE(sales.sale_date)'));
        })
        ->whereBetween('sales.sale_date', [$startDate, $endDate])
        ->select(
            DB::raw('DATE(sales.sale_date) as date'),
            DB::raw('COUNT(DISTINCT sales.id) as transaction_count'),
            DB::raw('SUM(sale_items.unit_price * sale_items.quantity_sold) as gross_revenue'),
            DB::raw('COALESCE(SUM(product_returns.total_refund_amount), 0) as returns_amount'),
            DB::raw('(SUM(sale_items.unit_price * sale_items.quantity_sold) - 
                    COALESCE(SUM(product_returns.total_refund_amount), 0)) as total_revenue') // Changed to total_revenue
        )
        ->groupBy(DB::raw('DATE(sales.sale_date)'))
        ->orderBy('date', 'asc')
        ->get();

    
        // Detailed Sales with pagination
        $detailedSales = DB::table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->leftJoin('payments', 'sales.id', '=', 'payments.sale_id')
            ->select(
                'sales.id',
                'sales.sale_date',
                'sales.customer_name',
                'sales.customer_contact',
                'users.f_name',
                'users.l_name',
                DB::raw('(SELECT COUNT(*) FROM sale_items WHERE sale_items.sale_id = sales.id) as items_count'),
                DB::raw('(SELECT SUM(unit_price * quantity_sold) FROM sale_items WHERE sale_items.sale_id = sales.id) as total_amount'),
                'payments.payment_method'
            )
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->orderBy('sales.sale_date', 'desc')
            ->paginate(10);
    
        // Product Performance (Net of Returns)
       // Product Performance (Net of Returns)
$productPerformance = DB::table('sale_items')
->join('products', 'sale_items.product_id', '=', 'products.id')
->join('sales', 'sale_items.sale_id', '=', 'sales.id')
->whereBetween('sales.sale_date', [$startDate, $endDate])
->select(
    'products.name',
    DB::raw('SUM(sale_items.quantity_sold) as total_quantity'),
    DB::raw('SUM(sale_items.unit_price * sale_items.quantity_sold) as gross_revenue'),
    DB::raw('COALESCE((SELECT SUM(ri.total_line_refund) FROM return_items ri JOIN product_returns pr ON ri.product_return_id = pr.id WHERE ri.product_id = products.id AND pr.created_at BETWEEN ? AND ?), 0) as returns_amount'),
    DB::raw('SUM(sale_items.unit_price * sale_items.quantity_sold) - COALESCE((SELECT SUM(ri.total_line_refund) FROM return_items ri JOIN product_returns pr ON ri.product_return_id = pr.id WHERE ri.product_id = products.id AND pr.created_at BETWEEN ? AND ?), 0) as total_revenue'),
    DB::raw('AVG(sale_items.unit_price) as avg_price')
)
->addBinding([$startDate, $endDate, $startDate, $endDate], 'select')
->groupBy('products.id', 'products.name')
->orderByDesc('total_revenue') // FIXED: Changed from 'net_revenue' to 'total_revenue'
->limit(10)
->get();
    
        // Category Analysis
        $categoryAnalysis = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity_sold) as total_quantity'),
                DB::raw('SUM(sale_items.unit_price * sale_items.quantity_sold) as total_revenue'),
                DB::raw('COUNT(DISTINCT sales.id) as transaction_count')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();
    
        return [
            'salesByDate' => $salesByDate,
            'detailedSales' => $detailedSales,
            'productPerformance' => $productPerformance,
            'categoryAnalysis' => $categoryAnalysis,
            'dateRange' => ['start' => $startDate, 'end' => $endDate]
        ];
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
            ->get();

        // Low Stock Alerts
        $lowStockAlerts = DB::table('products')
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
            ->limit(20)
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

        return [
            'stockLevels' => $stockLevels,
            'lowStockAlerts' => $lowStockAlerts,
            'stockMovement' => $stockMovement,
            'valuationReport' => $valuationReport
        ];
    }

    private function getFinancialReportsData($startDate, $endDate)
    {
        // Gross Revenue
        $grossRevenue = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->sum(DB::raw('sale_items.unit_price * sale_items.quantity_sold'));

        // Returns Amount
        $returnsAmount = DB::table('product_returns')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_refund_amount');

        // Net Revenue
        $netRevenue = $grossRevenue - $returnsAmount;

        // COGS (adjusted for returned items that were restocked)
        $grossCogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->sum(DB::raw('sale_items.quantity_sold * COALESCE(products.latest_unit_cost, 0)'));

        $returnedCogs = DB::table('return_items')
            ->join('product_returns', 'return_items.product_return_id', '=', 'product_returns.id')
            ->join('products', 'return_items.product_id', '=', 'products.id')
            ->whereBetween('product_returns.created_at', [$startDate, $endDate])
            ->where('return_items.inventory_adjusted', true)
            ->sum(DB::raw('COALESCE(products.latest_unit_cost, 0) * return_items.quantity_returned'));

        $netCogs = $grossCogs - $returnedCogs;
        $grossProfit = $netRevenue - $netCogs;

        // COGS Analysis
        $cogsAnalysis = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity_sold * COALESCE(products.latest_unit_cost, 0)) as total_cogs'),
                DB::raw('SUM(sale_items.unit_price * sale_items.quantity_sold) as total_revenue'),
                DB::raw('SUM(sale_items.quantity_sold) as total_quantity')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_cogs')
            ->get();

        // Payment Methods
        $paymentMethods = DB::table('payments')
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'payment_method',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount_tendered - change_given) as total_amount')
            )
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get();

        return [
            'profitLoss' => [
                'gross_revenue' => $grossRevenue,
                'returns_amount' => $returnsAmount,
                'net_revenue' => $netRevenue,
                'gross_cogs' => $grossCogs,
                'returned_cogs' => $returnedCogs,
                'net_cogs' => $netCogs,
                'grossProfit' => $grossProfit,
                'grossMargin' => $netRevenue > 0 ? ($grossProfit / $netRevenue) * 100 : 0
            ],
            'cogsAnalysis' => $cogsAnalysis,
            'paymentMethods' => $paymentMethods
        ];
    }

    private function getReturnsReportsData($startDate, $endDate)
    {
        // Returns Summary
        $returnsSummary = DB::table('product_returns')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('COUNT(*) as total_returns'),
                DB::raw('SUM(total_refund_amount) as total_refunds'),
                DB::raw('AVG(total_refund_amount) as avg_refund')
            )
            ->first();

        // Returns by Reason
        $returnsByReason = DB::table('product_returns')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                'return_reason',
                DB::raw('COUNT(*) as return_count'),
                DB::raw('SUM(total_refund_amount) as refund_amount')
            )
            ->groupBy('return_reason')
            ->get();

        // Returns by Product
        $returnsByProduct = DB::table('return_items')
            ->join('product_returns', 'return_items.product_return_id', '=', 'product_returns.id')
            ->join('products', 'return_items.product_id', '=', 'products.id')
            ->whereBetween('product_returns.created_at', [$startDate, $endDate])
            ->select(
                'products.name',
                DB::raw('SUM(return_items.quantity_returned) as total_quantity'),
                DB::raw('SUM(return_items.total_line_refund) as total_refund')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_refund')
            ->get();

        // Recent Returns
        $recentReturns = DB::table('product_returns')
            ->join('sales', 'product_returns.sale_id', '=', 'sales.id')
            ->join('users', 'product_returns.user_id', '=', 'users.id')
            ->whereBetween('product_returns.created_at', [$startDate, $endDate])
            ->select(
                'product_returns.id',
                'product_returns.created_at',
                'sales.id as sale_id',
                'sales.customer_name',
                'product_returns.total_refund_amount',
                'product_returns.return_reason',
                DB::raw('CONCAT(users.f_name, " ", users.l_name) as processed_by')
            )
            ->orderBy('product_returns.created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'summary' => $returnsSummary,
            'by_reason' => $returnsByReason,
            'by_product' => $returnsByProduct,
            'recent_returns' => $recentReturns
        ];
    }

    // Export methods can be added here later
    public function exportSalesReport(Request $request)
    {
        // PDF export functionality can be implemented here
    }

    public function exportInventoryReport(Request $request)
    {
        // PDF export functionality can be implemented here
    }

    public function exportFinancialReport(Request $request)
    {
        // PDF export functionality can be implemented here
    }
}