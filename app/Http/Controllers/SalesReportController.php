<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\PDF; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request or default to this month
        $dateRange = $request->get('date_range', 'thismonth');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Set dates based on selection
        list($startDate, $endDate) = $this->calculateDateRange($dateRange, $startDate, $endDate);

        // Get sales data
        $salesData = $this->getSalesReportsData($startDate, $endDate, true);
        $daysDiff = $startDate->diffInDays($endDate, false); // Get days difference
        $cleanDaysDiff = ceil(abs($daysDiff));

        return view('reports.sales.index', compact(
            'salesData',
            'dateRange',
            'startDate',
            'endDate',
            'daysDiff' 
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

    private function getSalesReportsData($startDate, $endDate, $paginate = true)
    {
        // Adaptive Sales Summary based on date range
        $salesSummary = $this->getAdaptiveSalesSummary($startDate, $endDate, $paginate);

        // Detailed Sales with pagination (only for web view)
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
            ->orderBy('sales.sale_date', 'desc');
        
        // Only paginate for web view, not for PDF
        if ($paginate) {
            $detailedSales = $detailedSales->paginate(10, ['*'], 'detailed_sales_page');
        } else {
            $detailedSales = $detailedSales->get();
        }

        // Product Performance (Net of Returns) - Ordered by Quantity Sold
        // Top Products by Quantity Sold
        $topProductsByQuantity = DB::table('sale_items')
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
        ->orderByDesc('total_quantity')
        ->limit(10)
        ->get();

        // Top Products by Revenue
        $topProductsByRevenue = DB::table('sale_items')
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
        ->orderByDesc('total_revenue')
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

        // Summary Statistics
        $summaryStats = DB::table('sales')
        ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
        ->leftJoin('product_returns', function ($join) use ($startDate, $endDate) {
            $join->on('product_returns.sale_id', '=', 'sales.id')
                ->whereBetween('product_returns.created_at', [$startDate, $endDate]);
        })
        ->whereBetween('sales.sale_date', [$startDate, $endDate])
        ->select(
            DB::raw('COUNT(DISTINCT sales.id) as total_transactions'),
            DB::raw('SUM(sale_items.quantity_sold) as total_items_sold'),
            DB::raw('SUM(sale_items.unit_price * sale_items.quantity_sold) as gross_revenue'),
            DB::raw('COALESCE(SUM(product_returns.total_refund_amount), 0) as total_returns'),
            DB::raw('SUM(sale_items.unit_price * sale_items.quantity_sold) - COALESCE(SUM(product_returns.total_refund_amount), 0) as net_revenue'),
            DB::raw('(SUM(sale_items.unit_price * sale_items.quantity_sold) - COALESCE(SUM(product_returns.total_refund_amount), 0)) / COUNT(DISTINCT sales.id) as avg_transaction_value')
        )
        ->first();

        $paymentMethods = $this->getPaymentMethodsData($startDate, $endDate);

        return [
            'salesSummary' => $salesSummary, 
            'detailedSales' => $detailedSales,
            'topProductsByQuantity' => $topProductsByQuantity,
            'topProductsByRevenue' => $topProductsByRevenue,
            'categoryAnalysis' => $categoryAnalysis,
            'paymentMethods' => $paymentMethods,
            'summaryStats' => $summaryStats,
            'dateRange' => ['start' => $startDate, 'end' => $endDate]
        ];
    }

    private function getAdaptiveSalesSummary($startDate, $endDate, $paginate = true)
    {
        // IMPORTANT: Calculate the DIFFERENCE between dates, not just check if daysDiff is 0
        $daysDiff = $startDate->diffInDays($endDate);
        
        // Debug: Check what's happening
        // \Log::info("Start Date: {$startDate}, End Date: {$endDate}, Days Diff: {$daysDiff}");
        
        // TODAY (single day) - Show hourly breakdown
        // Check if start and end are on the same day
        if ($startDate->isSameDay($endDate)) {
            // Today/Yesterday - Show hourly
            $query = DB::table('sales')
                ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
                ->leftJoin('product_returns', function ($join) use ($startDate) {
                    $join->on('product_returns.sale_id', '=', 'sales.id')
                        ->whereDate('product_returns.created_at', $startDate);
                })
                ->whereDate('sales.sale_date', $startDate)
                ->select(
                    DB::raw("DATE_FORMAT(sales.sale_date, '%h %p') as period"),
                    DB::raw('HOUR(sales.sale_date) as hour'),
                    DB::raw('COUNT(DISTINCT sales.id) as transaction_count'),
                    DB::raw('SUM(sale_items.unit_price * sale_items.quantity_sold) as gross_revenue'),
                    DB::raw('COALESCE(SUM(product_returns.total_refund_amount), 0) as returns_amount'),
                    DB::raw('(SUM(sale_items.unit_price * sale_items.quantity_sold) - 
                            COALESCE(SUM(product_returns.total_refund_amount), 0)) as total_revenue')
                )
                ->groupBy(DB::raw('HOUR(sales.sale_date)'), DB::raw("DATE_FORMAT(sales.sale_date, '%h %p')"))
                ->orderBy('hour', 'asc');
            
            if ($paginate) {
                return $query->paginate(24); // Show all 24 hours
            } else {
                return $query->get();
            }
        } 
        // 2-31 days - Show daily
        elseif ($daysDiff <= 31) {
            // 2-30 days (Week/Month) - Show daily
            $query = DB::table('sales')
                ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
                ->leftJoin('product_returns', function ($join) {
                    $join->on(DB::raw('DATE(product_returns.created_at)'), '=', DB::raw('DATE(sales.sale_date)'));
                })
                ->whereBetween('sales.sale_date', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(sales.sale_date) as period'),
                    DB::raw('COUNT(DISTINCT sales.id) as transaction_count'),
                    DB::raw('SUM(sale_items.unit_price * sale_items.quantity_sold) as gross_revenue'),
                    DB::raw('COALESCE(SUM(product_returns.total_refund_amount), 0) as returns_amount'),
                    DB::raw('(SUM(sale_items.unit_price * sale_items.quantity_sold) - 
                            COALESCE(SUM(product_returns.total_refund_amount), 0)) as total_revenue')
                )
                ->groupBy(DB::raw('DATE(sales.sale_date)'))
                ->orderBy('period', 'asc');
            
            if ($paginate) {
                return $query->paginate($daysDiff <= 7 ? 7 : 15);
            } else {
                return $query->get();
            }
        }
        // 32-365 days - Show monthly
        elseif ($daysDiff <= 366) {
            // 31-365 days (Year) - Show monthly
            $query = DB::table('sales')
                ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
                ->leftJoin('product_returns', function ($join) {
                    $join->on(DB::raw('YEAR(product_returns.created_at)'), '=', DB::raw('YEAR(sales.sale_date)'))
                        ->on(DB::raw('MONTH(product_returns.created_at)'), '=', DB::raw('MONTH(sales.sale_date)'));
                })
                ->whereBetween('sales.sale_date', [$startDate, $endDate])
                ->select(
                    DB::raw("DATE_FORMAT(sales.sale_date, '%M %Y') as period"),
                    DB::raw('YEAR(sales.sale_date) as year'),
                    DB::raw('MONTH(sales.sale_date) as month'),
                    DB::raw('COUNT(DISTINCT sales.id) as transaction_count'),
                    DB::raw('SUM(sale_items.unit_price * sale_items.quantity_sold) as gross_revenue'),
                    DB::raw('COALESCE(SUM(product_returns.total_refund_amount), 0) as returns_amount'),
                    DB::raw('(SUM(sale_items.unit_price * sale_items.quantity_sold) - 
                            COALESCE(SUM(product_returns.total_refund_amount), 0)) as total_revenue')
                )
                ->groupBy(DB::raw('YEAR(sales.sale_date)'), DB::raw('MONTH(sales.sale_date)'), DB::raw("DATE_FORMAT(sales.sale_date, '%M %Y')"))
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc');
            
            if ($paginate) {
                return $query->paginate(12);
            } else {
                return $query->get();
            }
        }
        // > 365 days - Show yearly
        else {
            // > 365 days (All Time) - Show yearly
            $query = DB::table('sales')
                ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
                ->leftJoin('product_returns', function ($join) {
                    $join->on(DB::raw('YEAR(product_returns.created_at)'), '=', DB::raw('YEAR(sales.sale_date)'));
                })
                ->whereBetween('sales.sale_date', [$startDate, $endDate])
                ->select(
                    DB::raw('YEAR(sales.sale_date) as period'),
                    DB::raw('COUNT(DISTINCT sales.id) as transaction_count'),
                    DB::raw('SUM(sale_items.unit_price * sale_items.quantity_sold) as gross_revenue'),
                    DB::raw('COALESCE(SUM(product_returns.total_refund_amount), 0) as returns_amount'),
                    DB::raw('(SUM(sale_items.unit_price * sale_items.quantity_sold) - 
                            COALESCE(SUM(product_returns.total_refund_amount), 0)) as total_revenue')
                )
                ->groupBy(DB::raw('YEAR(sales.sale_date)'))
                ->orderBy('period', 'asc');
            
            if ($paginate) {
                return $query->paginate(10);
            } else {
                return $query->get();
            }
        }
    }
    

    public function exportSummaryPDF(Request $request)
    {
        // Get date range from request
        $dateRange = $request->get('date_range', 'thismonth');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Set dates based on selection
        list($startDate, $endDate) = $this->calculateDateRange($dateRange, $startDate, $endDate);

        // Get sales data (same as index but without detailed sales)
        $salesData = $this->getSalesReportsData($startDate, $endDate, false);

        $daysDiff = $startDate->diffInDays($endDate);

        $data = [
            'salesData' => $salesData,
            'dateRange' => $dateRange,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'daysDiff' => $daysDiff, 
            'exportDate' => now()->format('M d, Y h:i A'),
        ];

        $pdf = PDF::loadView('reports.sales.exports.summary-pdf', $data);
        
        $filename = "sales-summary-{$startDate->format('Y-m-d')}-to-{$endDate->format('Y-m-d')}.pdf";
        
        return $pdf->download($filename);
    }

    public function exportDetailedCSV(Request $request)
    {
        // Get date range from request
        $dateRange = $request->get('date_range', 'thismonth');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Set dates based on selection
        list($startDate, $endDate) = $this->calculateDateRange($dateRange, $startDate, $endDate);

        // Get detailed sales with line items
        $detailedSales = DB::table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('payments', 'sales.id', '=', 'payments.sale_id')
            ->select(
                'sales.id as sale_id',
                'sales.sale_date as sale_datetime',
                DB::raw('CONCAT(users.f_name, " ", users.l_name) as cashier_name'),
                DB::raw('(SELECT SUM(unit_price * quantity_sold) FROM sale_items WHERE sale_items.sale_id = sales.id) as total_net_revenue'),
                'sales.customer_name',
                'sales.customer_contact',
                'payments.payment_method',
                'products.name as product_name',
                'sale_items.quantity_sold',
                DB::raw('sale_items.unit_price * sale_items.quantity_sold as line_item_total'),
                'sale_items.unit_price as unit_cost',
                'products.sku as product_sku'
            )
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->orderBy('sales.id', 'desc')
            ->orderBy('products.name', 'asc')
            ->get();

        $filename = "sales-detailed-{$startDate->format('Y-m-d')}-to-{$endDate->format('Y-m-d')}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($detailedSales) {
            $file = fopen('php://output', 'w');
            
            fwrite($file, "\xEF\xBB\xBF");
            
            fputcsv($file, [
                'sale_id',           
                'sale_datetime',     
                'cashier_name',      
                'total_net_revenue', 
                'customer_name',     
                'customer_contact',  
                'payment_method',    
                'product_name',      
                'product_sku',       
                'quantity_sold',     
                'line_item_total',   
                'unit_cost'          
            ]);
            
            // Data - each row represents a line item
            foreach ($detailedSales as $sale) {
                fputcsv($file, [
                    $sale->sale_id,                  
                    $sale->sale_datetime,              
                    $sale->cashier_name,               
                    $sale->total_net_revenue,          
                    $sale->customer_name ?? '',        
                    $sale->customer_contact ?? '',     
                    $sale->payment_method ?? '',       
                    $sale->product_name,               
                    $sale->product_sku ?? '',          
                    $sale->quantity_sold,              
                    $sale->line_item_total,            
                    $sale->unit_cost                   
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}