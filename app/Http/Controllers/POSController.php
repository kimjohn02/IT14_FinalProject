<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Payment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Exception;

class POSController extends Controller
{
    /**
     * Show the POS page
     */
    public function index()
    {
        return view('pos.index');
    }

    public function employeePos()
{
    // Employee POS - use employee layout
    return view('pos.index'); // This will use layouts.employee
}

    /**
     * Search products by name, SKU, or barcode
     */
    public function searchProduct(Request $request)
    {
        try {
            $searchTerm = $request->input('search_term');

            $products = Product::with('latestProductPrice')
                ->where('is_active', true)
                ->where(function($query) use ($searchTerm) {
                    $query->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('sku', 'like', '%' . $searchTerm . '%')
                        ->orWhere('manufacturer_barcode', 'like', '%' . $searchTerm . '%')
                        ->orWhere('model', 'like', '%' . $searchTerm . '%'); 
                })
                ->orderBy('name')
                ->limit(50)
                ->get()
                ->map(function($product) {
                    $hasPrice = !is_null($product->latestProductPrice);
                    $stockStatus = $product->quantity_in_stock <= 0 ? 'out_of_stock' : 
                                ($product->quantity_in_stock <= 10 ? 'low_stock' : 'in_stock');
                    
                    return [
                        'id' => $product->id,
                        'text' => $this->formatProductText($product),
                        'name' => $product->name,
                        'model' => $product->model,
                        'sku' => $product->sku,
                        'barcode' => $product->manufacturer_barcode,
                        'stock' => $product->quantity_in_stock,
                        'price' => $hasPrice ? $product->latestProductPrice->retail_price : null,
                        'has_price' => $hasPrice,
                        'stock_status' => $stockStatus,
                        'data_attributes' => [
                            'model' => $product->model,
                            'barcode' => $product->manufacturer_barcode,
                            'stock' => $product->quantity_in_stock,
                            'price' => $hasPrice ? $product->latestProductPrice->retail_price : null,
                            'has_price' => $hasPrice
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'products' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'products' => [],
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function formatProductText($product)
    {
        $hasPrice = !is_null($product->latestProductPrice);
        $price = $hasPrice ? '₱' . number_format($product->latestProductPrice->retail_price, 2) : 'No Price';
        $stock = $product->quantity_in_stock;
        
        $text = $product->name;
        $text .= " [" . $product->sku . "]";
        
        if ($product->model) {
            $text .= " ({$product->model})";
        }
        
        if ($product->manufacturer_barcode) {
            $text .= " [{$product->manufacturer_barcode}]";
        }
        
        $text .= " | Stock: {$stock} | Price: {$price}";
        
        return $text;
    }

    /**
     * Complete the sale: create Sale, SaleItems, and Payment in DB
     */
    public function completeSale(Request $request)
    {
        try {
            DB::beginTransaction();

            $items = $request->input('items'); // frontend sends full cart
            $paymentMethod = $request->input('payment_method');
            $amountTendered = $request->input('amount_tendered');
            $referenceNo = $request->input('reference_no');
            $customerName = $request->input('customer_name');
            $customerContact = $request->input('customer_contact');

            if (empty($items)) {
                throw new Exception('Cart is empty.');
            }

            // Calculate total
            $total = 0;
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product']['id']);
                $qty = $item['quantity_sold'];
                $price = $product->latestProductPrice->retail_price;

                if ($product->quantity_in_stock < $qty) {
                    throw new Exception("Insufficient stock for {$product->name}");
                }

                $total += $qty * $price;
            }

            // Validate payment
            if ($paymentMethod === 'Cash') {
                if ($amountTendered < $total) {
                    throw new Exception('Amount tendered must be greater than or equal to total.');
                }
                $change = $amountTendered - $total;
            } else {
                // GCash or Card
                if ($amountTendered != $total) {
                    throw new Exception('Amount tendered must equal total for ' . $paymentMethod);
                }
                if (empty($referenceNo)) {
                    throw new Exception('Reference number is required for ' . $paymentMethod);
                }
                $change = 0;
            }

            // Create Sale
            $sale = Sale::create([
                'user_id' => session('user_id'),
                'sale_date' => now(),
                'customer_name' => $customerName,
                'customer_contact' => $customerContact
            ]);

            // Create SaleItems & update stock
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product']['id']);
                $qty = $item['quantity_sold'];
                $price = $product->latestProductPrice->retail_price;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity_sold' => $qty,
                    'unit_price' => $price
                ]);

                $product->decrement('quantity_in_stock', $qty);
            }

            // Create Payment
            Payment::create([
                'sale_id' => $sale->id,
                'payment_date' => now(),
                'payment_method' => $paymentMethod,
                'amount_tendered' => $amountTendered,
                'change_given' => $change,
                'reference_no' => $paymentMethod === 'Cash' ? null : $referenceNo
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'sale' => $sale->load('items.product', 'payment'),
                'change' => $change,
                'message' => 'Sale completed successfully'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Download receipt PDF
     */
    public function downloadReceiptPDF(Sale $sale)
    {
        $sale->load(['items.product', 'payment', 'user']);

        if (!$sale->payment) {
            abort(404, "Payment not found.");
        }

        $pdf = Pdf::loadView('pos.receipt', compact('sale'))
                ->setPaper([0, 0, 226.77, 1000]); // 80mm wide, variable height

        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
        ]);

        // Stream to browser (inline)
        return $pdf->download("receipt-{$sale->id}.pdf");
    }

    public function myTransactions(Request $request)
    {
        // Get user_id from session
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect('/login')->with('error', 'Please login first.');
        }

        // Get user details for display
        $user = User::find($userId);
        if (!$user) {
            return redirect('/login')->with('error', 'User not found.');
        }

        // Base query - TODAY'S SALES ONLY
        $query = Sale::with(['items.product', 'payment'])
            ->where('user_id', $userId)
            ->whereDate('sale_date', Carbon::today())
            ->latest('sale_date');

        // Clone query for summary BEFORE pagination
        $summaryQuery = clone $query;

        // Search by Sale ID only (no other filters needed)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('id', 'LIKE', "%{$search}%");
            $summaryQuery->where('id', 'LIKE', "%{$search}%");
        }

        // Pagination for table (10 per page)
        $sales = $query->paginate(10)->withQueryString();

        // Summary for today (all matching sales, not just current page)
        $allSalesToday = $summaryQuery->get();

        $todaySummary = $allSalesToday->sum(function($sale) {
            return $sale->items->sum(function($item) {
                return $item->quantity_sold * $item->unit_price;
            });
        });

        $totalSalesToday = $allSalesToday->count();

        $totalItemsToday = $allSalesToday->sum(function($sale) {
            return $sale->items->sum('quantity_sold');
        });

        return view('pos.my-transactions', compact(
            'sales', 
            'todaySummary', 
            'user',
            'totalSalesToday',
            'totalItemsToday'
        ));
    }

    /**
     * Get TODAY'S sale details for cashier
     */
    public function saleDetails($id)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $sale = Sale::with(['items.product', 'payment'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->whereDate('sale_date', Carbon::today()) // TODAY ONLY
            ->first();

        if (!$sale) {
            return response()->json(['error' => 'Sale not found or unauthorized'], 404);
        }

        return response()->json($sale);
    }

    /**
     * Generate receipt PDF for cashier's TODAY'S sales only
     */
    public function receiptPdf($id)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            abort(401, 'Unauthorized');
        }
        
        $sale = Sale::with(['user', 'items.product', 'payment'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->whereDate('sale_date', Carbon::today()) // TODAY ONLY
            ->first();

        if (!$sale) {
            abort(404, 'Receipt not found or unauthorized');
        }

        $pdf = PDF::loadView('pos.receipt', compact('sale'));

    // Use a fixed width but a very generous height. 
    // 'cm' or 'mm' are more stable in dompdf than raw points.
    $pdf->setPaper('80mm', 'portrait'); 

    $pdf->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'defaultFont' => 'DejaVu Sans',
        'dpi' => 96, 
        'defaultPaperSize' => 'a4', // Fallback
    ]);

    return $pdf->stream("receipt-{$sale->id}.pdf");
    }

    public function printReceipt($id)
{
    $userId = session('user_id');

    if (!$userId) {
        abort(401, 'Unauthorized');
    }

    $sale = Sale::with(['user', 'items.product', 'payment'])
        ->where('id', $id)
        ->where('user_id', $userId)
        ->whereDate('sale_date', Carbon::today())
        ->firstOrFail();

    return view('pos.receipt-print', compact('sale'));
}


}