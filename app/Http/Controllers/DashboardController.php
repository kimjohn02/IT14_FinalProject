<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get date range based on filter
        $dateRange = $this->getDateRangeFromFilter($request);
        $startDate = $dateRange['start_date'];
        $endDate = $dateRange['end_date'];
        $filterType = $dateRange['filter_type'];

        // Total Revenue (Net of Returns)
        $totalRevenue = $this->calculateNetRevenue($startDate, $endDate);

        // Total Sales Transactions
        $totalTransactions = DB::table('sales')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->count();

        // Average Order Value
        $averageOrderValue = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // Gross Profit (Net of Returns)
        $grossProfit = $this->calculateGrossProfit($startDate, $endDate);

        // Inventory Value
        $inventoryValue = $this->calculateInventoryValue();

        // Sales Trend Data (Net of Returns)
        $salesTrend = $this->getSalesTrend($startDate, $endDate, $filterType);

        // Top Products (Net of Returns)
        $topProducts = $this->getTopProducts($startDate, $endDate);

        $paymentMethods = $this->getPaymentMethodsData($startDate, $endDate);

        // Sales by Category (Net of Returns)
        $categorySales = $this->getCategorySales($startDate, $endDate);

        // Low Stock Alerts
        $lowStockAlerts = $this->getLowStockAlerts();

        // Recent Adjustments
        $recentAdjustments = $this->getRecentAdjustments();

        // Returns Data
        $returnsData = $this->getReturnsData($startDate, $endDate);

        $daysDiff = $startDate->diffInDays($endDate);
        $currentChartType = 'weekly'; // default
        
        if ($daysDiff > 730) { // More than 2 years
            $currentChartType = 'yearly';
        } elseif ($daysDiff > 60) { // More than 2 months
            $currentChartType = 'monthly';
        } elseif ($daysDiff > 14) { // More than 2 weeks
            $currentChartType = 'weekly';
        } elseif ($daysDiff > 1) { // More than 1 day
            $currentChartType = 'daily';
        } else {
            $currentChartType = 'hourly';
        }

        return view('dashboard', compact(
            'totalRevenue',
            'grossProfit',
            'averageOrderValue',
            'totalTransactions',
            'inventoryValue',
            'salesTrend',
            'topProducts',
            'categorySales',
            'lowStockAlerts',
            'recentAdjustments',
            'returnsData',
            'paymentMethods',
            'startDate',
            'endDate',
                    'currentChartType' // Add this
        ));
    }

    private function getDateRangeFromFilter(Request $request)
    {
        $filter = $request->get('filter', 'this_month');
        $filterType = $request->get('filter_type', 'preset');

        if ($filterType === 'custom') {
            $startDate = Carbon::parse($request->get('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->get('end_date'))->endOfDay();
        } else {
            switch ($filter) {
                case 'today':
                    $startDate = Carbon::today()->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    break;
                case 'this_week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'last_month': 
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'this_year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
                case 'all_time':
                    // Get the earliest sale date or 1 year ago if no sales yet
                    $earliestDate = DB::table('sales')
                        ->min('sale_date');
                    
                    if ($earliestDate) {
                        $startDate = Carbon::parse($earliestDate)->startOfDay();
                    } else {
                        // If no sales yet, default to 1 year ago
                        $startDate = Carbon::now()->subYear()->startOfDay();
                    }
                    
                    $endDate = Carbon::now()->endOfDay();
                    break;
                case 'this_month':
                default:
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
            }
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'filter_type' => $filterType
        ];
    }

    private function calculateNetRevenue($startDate, $endDate)
    {
        // Gross Sales
        $grossSales = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->sum(DB::raw('sale_items.unit_price * sale_items.quantity_sold'));

        // Returns Amount
        $returnsAmount = DB::table('product_returns')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_refund_amount');

        return $grossSales - $returnsAmount;
    }

    private function calculateGrossProfit($startDate, $endDate)
    {
        // Net Revenue
        $netRevenue = $this->calculateNetRevenue($startDate, $endDate);

        // COGS (Cost of Goods Sold) - Adjusted for returns
        $grossCogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->sum(DB::raw('COALESCE(products.latest_unit_cost, 0) * sale_items.quantity_sold'));

        // COGS from returned items (items that were restocked)
        $returnedCogs = DB::table('return_items')
            ->join('product_returns', 'return_items.product_return_id', '=', 'product_returns.id')
            ->join('products', 'return_items.product_id', '=', 'products.id')
            ->whereBetween('product_returns.created_at', [$startDate, $endDate])
            ->where('return_items.inventory_adjusted', true) // Only resaleable items
            ->sum(DB::raw('COALESCE(products.latest_unit_cost, 0) * return_items.quantity_returned'));

        $netCogs = $grossCogs - $returnedCogs;

        return $netRevenue - $netCogs;
    }

    private function getSalesTrend($startDate, $endDate, $filterType)
    {
        $daysDiff = $startDate->diffInDays($endDate);
        
        if ($daysDiff > 730) { // More than 2 years
            return $this->getYearlySalesData($startDate, $endDate);
        } elseif ($daysDiff > 60) { // More than 2 months
            return $this->getMonthlySalesData($startDate, $endDate);
        } elseif ($daysDiff > 14) { // More than 2 weeks
            return $this->getWeeklySalesData($startDate, $endDate);
        } elseif ($daysDiff > 1) { // More than 1 day
            return $this->getDailySalesData($startDate, $endDate);
        } else {
            // Single day - show hourly
            return $this->getHourlySalesData($startDate, $endDate);
        }
    }

    private function getHourlySalesData($startDate, $endDate)
    {
        $labels = [];
        $data = [];
        
        // For today, show hours from store opening (e.g., 8 AM) to closing (e.g., 8 PM)
        // Or show all 24 hours if you want
        $openingHour = 8; // 8 AM
        $closingHour = 20; // 8 PM
        
        for ($hour = $openingHour; $hour <= $closingHour; $hour++) {
            $hourStart = $startDate->copy()->setHour($hour)->setMinute(0)->setSecond(0);
            $hourEnd = $hourStart->copy()->addHour();
            
            // Format label: 8 AM, 9 AM, etc.
            $labels[] = $hourStart->format('g A');
            
            // Get sales for this hour
            $hourlySales = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereBetween('sales.sale_date', [$hourStart, $hourEnd])
                ->sum(DB::raw('sale_items.unit_price * sale_items.quantity_sold'));
            
            // Get returns for this hour
            $hourlyReturns = DB::table('product_returns')
                ->whereBetween('created_at', [$hourStart, $hourEnd])
                ->sum('total_refund_amount');
            
            $data[] = $hourlySales - $hourlyReturns;
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getDailySalesData($startDate, $endDate)
    {
        $dates = [];
        $data = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('M d');
            
            // Net sales for the day (sales minus returns)
            $dailySales = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereDate('sales.sale_date', $currentDate->format('Y-m-d'))
                ->sum(DB::raw('sale_items.unit_price * sale_items.quantity_sold'));
            
            $dailyReturns = DB::table('product_returns')
                ->whereDate('created_at', $currentDate->format('Y-m-d'))
                ->sum('total_refund_amount');
            
            $data[] = $dailySales - $dailyReturns;
            $currentDate->addDay();
        }

        return [
            'labels' => $dates,
            'data' => $data
        ];
    }

    private function getWeeklySalesData($startDate, $endDate)
    {
        $labels = [];
        $data = [];
        $currentWeek = $startDate->copy();

        while ($currentWeek <= $endDate) {
            $weekStart = $currentWeek->copy()->startOfWeek();
            $weekEnd = $currentWeek->copy()->endOfWeek();
            
            $labels[] = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d');
            
            $weeklySales = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereBetween('sales.sale_date', [$weekStart, $weekEnd])
                ->sum(DB::raw('sale_items.unit_price * sale_items.quantity_sold'));
            
            $weeklyReturns = DB::table('product_returns')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum('total_refund_amount');
            
            $data[] = $weeklySales - $weeklyReturns;
            $currentWeek->addWeek();
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getMonthlySalesData($startDate, $endDate)
    {
        $labels = [];
        $data = [];
        $currentMonth = $startDate->copy();

        while ($currentMonth <= $endDate) {
            $monthStart = $currentMonth->copy()->startOfMonth();
            $monthEnd = $currentMonth->copy()->endOfMonth();
            
            $labels[] = $monthStart->format('M Y');
            
            $monthlySales = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereBetween('sales.sale_date', [$monthStart, $monthEnd])
                ->sum(DB::raw('sale_items.unit_price * sale_items.quantity_sold'));
            
            $monthlyReturns = DB::table('product_returns')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total_refund_amount');
            
            $data[] = $monthlySales - $monthlyReturns;
            $currentMonth->addMonth();
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function calculateInventoryValue()
    {
        $inventoryValue = DB::table('products')
            ->where('is_active', true)
            ->sum(DB::raw('quantity_in_stock * COALESCE(latest_unit_cost, 0)'));

        return $inventoryValue;
    }

    private function getTopProducts($startDate, $endDate)
    {
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity_sold) as total_quantity')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        return [
            'labels' => $topProducts->pluck('name')->toArray(),
            'data' => $topProducts->pluck('total_quantity')->toArray()
        ];
    }

    private function getPaymentMethodsData($startDate, $endDate)
    {
        return DB::table('payments')
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
    }

    private function getCategorySales($startDate, $endDate)
    {
        $categorySales = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'categories.name',
                DB::raw('SUM(sale_items.quantity_sold) as total_sales')
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();

        return [
            'labels' => $categorySales->pluck('name')->toArray(),
            'data' => $categorySales->pluck('total_sales')->toArray()
        ];
    }

    private function getLowStockAlerts()
{
    // First get the total count
    $totalCount = DB::table('products')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->where('products.is_active', true)
        ->where(function($query) {
            $query->where('products.quantity_in_stock', '<=', DB::raw('products.reorder_level'))
                  ->orWhere('products.quantity_in_stock', 0);
        })
        ->count();

    // Get out of stock count for the alert banner
    $outOfStockCount = DB::table('products')
        ->where('products.is_active', true)
        ->where('products.quantity_in_stock', 0)
        ->count();

    // Get limited results for display (10 rows)
    $alerts = DB::table('products')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->where('products.is_active', true)
        ->where(function($query) {
            $query->where('products.quantity_in_stock', '<=', DB::raw('products.reorder_level'))
                  ->orWhere('products.quantity_in_stock', 0);
        })
        ->select(
            'products.name',
            'products.quantity_in_stock as current_stock',
            'products.reorder_level',
            'categories.name as category_name'
        )
        ->orderBy('products.quantity_in_stock')
        ->limit(10)
        ->get();

    return [
        'total_count' => $totalCount,
        'out_of_stock_count' => $outOfStockCount,
        'alerts' => $alerts
    ];
}

    private function getRecentAdjustments()
    {
        return DB::table('stock_adjustments')
            ->join('stock_adjustment_items', 'stock_adjustments.id', '=', 'stock_adjustment_items.stock_adjustment_id')
            ->join('products', 'stock_adjustment_items.product_id', '=', 'products.id')
            ->where('stock_adjustments.adjustment_date', '>=', Carbon::now()->subDays(7))
            ->select(
                'stock_adjustments.adjustment_date',
                'stock_adjustments.adjustment_type',
                'stock_adjustments.reason_notes',
                'stock_adjustment_items.quantity_change',
                'products.name as product_name'
            )
            ->orderBy('stock_adjustments.adjustment_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                $item->adjustment_date = Carbon::parse($item->adjustment_date);
                return $item;
            });
    }

    private function getReturnsData($startDate, $endDate)
    {
        $totalReturns = DB::table('product_returns')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $totalRefunds = DB::table('product_returns')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_refund_amount');

        $returnsByReason = DB::table('product_returns')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('return_reason', DB::raw('COUNT(*) as count'))
            ->groupBy('return_reason')
            ->get();

        return [
            'total_returns' => $totalReturns,
            'total_refunds' => $totalRefunds,
            'returns_by_reason' => $returnsByReason
        ];
    }
    
   public function getSalesChartData(Request $request)
    {
        $chartType = $request->get('chart_type', 'auto');
        
        // Get date range based on filter
        $dateRange = $this->getDateRangeFromFilter($request);
        $startDate = $dateRange['start_date'];
        $endDate = $dateRange['end_date'];
        
        // If chart_type is 'auto', determine best granularity
        if ($chartType === 'auto') {
            $daysDiff = $startDate->diffInDays($endDate);
            
            if ($daysDiff > 730) { // More than 2 years
                $chartType = 'yearly';
            } elseif ($daysDiff > 60) { // More than 2 months
                $chartType = 'monthly';
            } elseif ($daysDiff > 14) { // More than 2 weeks
                $chartType = 'weekly';
            } elseif ($daysDiff > 1) { // More than 1 day
                $chartType = 'daily';
            } else {
                $chartType = 'hourly';
            }
        }
        
        // Get sales data based on chart type
        switch ($chartType) {
            case 'hourly':
                $salesData = $this->getHourlySalesData($startDate, $endDate);
                break;
            case 'weekly':
                $salesData = $this->getWeeklySalesData($startDate, $endDate);
                break;
            case 'monthly':
                $salesData = $this->getMonthlySalesData($startDate, $endDate);
                break;
            case 'yearly':
                $salesData = $this->getYearlySalesData($startDate, $endDate);
                break;
            case 'daily':
            default:
                $salesData = $this->getDailySalesData($startDate, $endDate);
                break;
        }
        
        return response()->json($salesData);
    }
    
    private function getYearlySalesData($startDate, $endDate)
    {
        $labels = [];
        $data = [];
        $currentYear = $startDate->copy();
    
        while ($currentYear <= $endDate) {
            $yearStart = $currentYear->copy()->startOfYear();
            $yearEnd = $currentYear->copy()->endOfYear();
            
            $labels[] = $yearStart->format('Y');
            
            $yearlySales = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereBetween('sales.sale_date', [$yearStart, $yearEnd])
                ->sum(DB::raw('sale_items.unit_price * sale_items.quantity_sold'));
            
            $yearlyReturns = DB::table('product_returns')
                ->whereBetween('created_at', [$yearStart, $yearEnd])
                ->sum('total_refund_amount');
            
            $data[] = $yearlySales - $yearlyReturns;
            $currentYear->addYear();
        }
    
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}