<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB; 

class SaleController extends Controller
{
      public function index(Request $request)
    {
        $query = Sale::with(['user', 'items.product', 'payment']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%");
            });
        }

        // Payment method filter
        if ($request->filled('payment_method')) {
            $query->whereHas('payment', function($q) use ($request) {
                $q->where('payment_method', $request->payment_method);
            });
        }

        // Date filter logic
        $this->applyDateFilter($query, $request);

        // Sorting
        $sort = $request->get('sort', 'sale_date');
        $direction = $request->get('direction', 'desc');
        
        $allowedSorts = ['id', 'sale_date'];
        
        if (in_array($sort, $allowedSorts)) {
            // Direct column sorts
            $query->orderBy($sort, $direction);
        } elseif ($sort == 'total_amount') {
            // Sort by calculated total amount
            $query->addSelect(['total_amount' => function($q) {
                $q->selectRaw('COALESCE(SUM(quantity_sold * unit_price), 0)')
                  ->from('sale_items')
                  ->whereColumn('sale_id', 'sales.id');
            }])->orderBy('total_amount', $direction);
        } else {
            // Default sort
            $query->orderBy('sale_date', 'desc');
        }

        $sales = $query->paginate(20);

        return view('sales.index', compact('sales', 'sort', 'direction'));
    }

    public function show($id)
    {
        $sale = Sale::with(['user', 'items.product', 'payment'])
            ->findOrFail($id);

        return response()->json($sale);
    }

    public function receipt($id)
    {
        $sale = Sale::with(['user', 'items.product', 'payment'])->findOrFail($id);

        $pdf = PDF::loadView('pos.receipt', compact('sale'));
        
        $pdf->setPaper([0, 0, 226.77, 1000]);

        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'margin-top'    => 0,
            'margin-right' => 2, 
            'margin-left' => 2,
            'margin-bottom' => 0,
        ]);

        return $pdf->stream("receipt-{$sale->id}.pdf");
    }

    public function printReceipt($id)
    {
        $sale = Sale::with(['user', 'items.product', 'payment'])
            ->findOrFail($id);

        return view('pos.receipt-print', compact('sale'));
    }


    public function details($id)
    {
        $sale = Sale::with(['user', 'items.product', 'payment'])
            ->findOrFail($id);

        return response()->json($sale);
    }

    private function applyDateFilter($query, $request)
    {
        $dateFilter = $request->input('date_filter');
        
        if ($dateFilter) {
            $today = Carbon::today();
            
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('sale_date', $today);
                    break;
                case 'this_week':
                    $query->whereBetween('sale_date', [
                        $today->copy()->startOfWeek(),
                        $today->copy()->endOfWeek()
                    ]);
                    break;
                case 'this_month':
                    $query->whereBetween('sale_date', [
                        $today->copy()->startOfMonth(),
                        $today->copy()->endOfMonth()
                    ]);
                    break;
                case 'this_year':
                    $query->whereBetween('sale_date', [
                        $today->copy()->startOfYear(),
                        $today->copy()->endOfYear()
                    ]);
                    break;
                case 'last_week':
                    $query->whereBetween('sale_date', [
                        $today->copy()->subWeek()->startOfWeek(),
                        $today->copy()->subWeek()->endOfWeek()
                    ]);
                    break;
                case 'last_month':
                    $query->whereBetween('sale_date', [
                        $today->copy()->subMonth()->startOfMonth(),
                        $today->copy()->subMonth()->endOfMonth()
                    ]);
                    break;
                case 'last_year':
                    $query->whereBetween('sale_date', [
                        $today->copy()->subYear()->startOfYear(),
                        $today->copy()->subYear()->endOfYear()
                    ]);
                    break;
            }
        } else {
            if ($request->filled('start_date')) {
                $query->whereDate('sale_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('sale_date', '<=', $request->end_date);
            }
        }
        
        return $query;
    }

    public function exportCsv(Request $request)
    {
        // Use DB query like reports page to get line items
        $query = DB::table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('payments', 'sales.id', '=', 'payments.sale_id')
            ->select(
                'sales.id as sale_id',
                'sales.sale_date as sale_datetime',
                DB::raw('CONCAT(users.f_name, " ", users.l_name) as cashier_name'),
                DB::raw('(SELECT SUM(unit_price * quantity_sold) FROM sale_items WHERE sale_items.sale_id = sales.id) as total_amount'),
                'sales.customer_name',
                'sales.customer_contact',
                'payments.payment_method',
                'payments.amount_tendered',
                'payments.change_given',
                'payments.reference_no',
                'products.name as product_name',
                'sale_items.quantity_sold',
                DB::raw('sale_items.unit_price * sale_items.quantity_sold as line_item_total'),
                'sale_items.unit_price as unit_price',
                'products.sku as product_sku'
            );

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('sales.id', 'LIKE', "%{$search}%");
        }

        if ($request->filled('payment_method')) {
            $query->where('payments.payment_method', $request->payment_method);
        }

        // Date filter logic
        $this->applyDateFilter($query, $request, 'sales');

        // Sorting
        $sort = $request->get('sort', 'sales.sale_date');
        $direction = $request->get('direction', 'desc');
        
        // Handle sorting for line items export
        if ($sort == 'total_amount') {
            // For line items, we need a different approach
            $query->addSelect(DB::raw('
                (SELECT SUM(unit_price * quantity_sold) 
                FROM sale_items 
                WHERE sale_items.sale_id = sales.id) as sale_total
            '))->orderBy('sale_total', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        // Also order by product name for consistent line item ordering
        $query->orderBy('products.name', 'asc');

        $sales = $query->get();

        $filename = "sales-history-" . now()->format('Y-m-d-H-i-s') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");
            
            // Headers - line item format like reports
            fputcsv($file, [
                'Sale ID',
                'Date & Time',
                'Cashier',
                'Customer Name',
                'Customer Contact',
                'Payment Method',
                'Amount Tendered',
                'Change Given',
                'Reference Number',
                'Product Name',
                'Product SKU',
                'Quantity Sold',
                'Unit Price',
                'Line Item Total',
                'Sale Total Amount'
            ]);
            
            // Data rows - one row per line item
            foreach ($sales as $sale) {
                fputcsv($file, [
                    '#' . $sale->sale_id,
                    $sale->sale_datetime,
                    $sale->cashier_name ?? 'N/A',
                    $sale->customer_name ?? 'N/A',
                    $sale->customer_contact ?? 'N/A',
                    $sale->payment_method ?? 'N/A',
                    $sale->amount_tendered ? '₱' . number_format($sale->amount_tendered, 2) : 'N/A',
                    $sale->change_given ? '₱' . number_format($sale->change_given, 2) : 'N/A',
                    $sale->reference_no ?? 'N/A',
                    $sale->product_name,
                    $sale->product_sku ?? '',
                    $sale->quantity_sold,
                    '₱' . number_format($sale->unit_price, 2),
                    '₱' . number_format($sale->line_item_total, 2),
                    '₱' . number_format($sale->total_amount, 2)
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}