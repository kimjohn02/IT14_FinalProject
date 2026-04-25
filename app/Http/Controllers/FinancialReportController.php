<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request or default to this month
        $dateRange = $request->get('date_range', 'thismonth');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Set dates based on selection
        list($startDate, $endDate) = $this->calculateDateRange($dateRange, $startDate, $endDate);

        // Get financial data
        $financialData = $this->getFinancialReportsData($startDate, $endDate);

        return view('reports.financial.index', compact(
            'financialData',
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

        // Additional Financial Metrics
        $totalTransactions = DB::table('sales')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->count();

        $averageTransactionValue = $totalTransactions > 0 ? $netRevenue / $totalTransactions : 0;

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
            'additionalMetrics' => [
                'total_transactions' => $totalTransactions,
                'average_transaction_value' => $averageTransactionValue,
                'returns_percentage' => $grossRevenue > 0 ? ($returnsAmount / $grossRevenue) * 100 : 0
            ]
        ];
    }

    public function exportFullReport(Request $request)
    {
        // Get date range from request
        $dateRange = $request->get('date_range', 'thismonth');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Set dates based on selection
        list($startDate, $endDate) = $this->calculateDateRange($dateRange, $startDate, $endDate);

        // Get financial data
        $financialData = $this->getFinancialReportsData($startDate, $endDate);

        $data = [
            'financialData' => $financialData,
            'dateRange' => $dateRange,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'exportDate' => now()->format('M d, Y h:i A'),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.financial.exports.full-pdf', $data);
        
        $filename = "Financial_Report_{$startDate->format('M_Y')}.pdf";
        
        return $pdf->download($filename);
    }

    public function exportProfitLossCSV(Request $request)
    {
        // Get date range from request
        $dateRange = $request->get('date_range', 'thismonth');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Set dates based on selection
        list($startDate, $endDate) = $this->calculateDateRange($dateRange, $startDate, $endDate);

        // Get financial data
        $financialData = $this->getFinancialReportsData($startDate, $endDate);

        $filename = "P_L_Summary_{$startDate->format('Y-m-d')}_to_{$endDate->format('Y-m-d')}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($financialData, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($file, [
                'Start_Date', 'End_Date', 'Gross_Revenue', 'Returns_Refunds_Amount', 
                'Net_Revenue', 'Gross_COGS', 'Returned_COGS_Credit', 'Net_COGS', 
                'Gross_Profit', 'Gross_Margin_Percentage'
            ]);
            
            // Data
            $pl = $financialData['profitLoss'];
            fputcsv($file, [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
                $pl['gross_revenue'],
                $pl['returns_amount'],
                $pl['net_revenue'],
                $pl['gross_cogs'],
                $pl['returned_cogs'],
                $pl['net_cogs'],
                $pl['grossProfit'],
                $pl['grossMargin']
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportCogsAnalysisCSV(Request $request)
    {
        // Get date range from request
        $dateRange = $request->get('date_range', 'thismonth');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Set dates based on selection
        list($startDate, $endDate) = $this->calculateDateRange($dateRange, $startDate, $endDate);

        // Get financial data
        $financialData = $this->getFinancialReportsData($startDate, $endDate);

        $filename = "COGS_Analysis_Category_{$startDate->format('Y-m-d')}_to_{$endDate->format('Y-m-d')}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($financialData) {
            $file = fopen('php://output', 'w');
            
            fwrite($file, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($file, [
                'Category_Name', 'Total_Quantity_Sold', 'Total_Revenue', 'Total_COGS',
                'Gross_Profit', 'Gross_Margin_Percentage', 'Avg_Selling_Price', 'Avg_Unit_Cost'
            ]);
            
            // Data
            foreach ($financialData['cogsAnalysis'] as $analysis) {
                $profit = $analysis->total_revenue - $analysis->total_cogs;
                $margin = $analysis->total_revenue > 0 ? ($profit / $analysis->total_revenue) * 100 : 0;
                $avgPrice = $analysis->total_quantity > 0 ? $analysis->total_revenue / $analysis->total_quantity : 0;
                $avgCost = $analysis->total_quantity > 0 ? $analysis->total_cogs / $analysis->total_quantity : 0;
                
                fputcsv($file, [
                    $analysis->category_name,
                    $analysis->total_quantity,
                    $analysis->total_revenue,
                    $analysis->total_cogs,
                    $profit,
                    $margin,
                    $avgPrice,
                    $avgCost
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}