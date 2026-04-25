<?php

namespace App\Http\Controllers;

use App\Models\ProductReturn;
use App\Models\ReturnItem;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReturnController extends Controller
{
    public function index(Request $request)
{
    $query = ProductReturn::with(['sale', 'user', 'returnItems.product']);

   // Search
    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        
        // Extract numbers from search (handles "123", "#123", "Sale 123", etc.)
        preg_match_all('/\d+/', $search, $matches);
        $saleId = $matches[0][0] ?? null;
        
        if ($saleId) {
            // Search ONLY by Sale ID (exact match)
            $query->whereHas('sale', function($q2) use ($saleId) {
                $q2->where('id', $saleId);
            });
        } else {
            // Return no results if search doesn't contain a number
            $query->whereRaw('1 = 0');
        }
    }

    // Return Reason filter
    if ($request->has('return_reason') && $request->return_reason != '') {
        $query->where('return_reason', $request->return_reason);
    }

    // Date filter logic
    $dateFilter = $request->input('date_filter');
    
    if ($dateFilter) {
        $today = Carbon::today();
        
        switch ($dateFilter) {
            case 'today':
                $query->whereDate('created_at', $today);
                break;
                
            case 'this_week':
                $query->whereBetween('created_at', [
                    $today->copy()->startOfWeek(),
                    $today->copy()->endOfWeek()
                ]);
                break;
                
            case 'this_month':
                $query->whereBetween('created_at', [
                    $today->copy()->startOfMonth(),
                    $today->copy()->endOfMonth()
                ]);
                break;
                
            case 'this_year':
                $query->whereBetween('created_at', [
                    $today->copy()->startOfYear(),
                    $today->copy()->endOfYear()
                ]);
                break;
        }
        
        // Clear any custom date range values when quick filter is used
        $request->merge([
            'start_date' => null,
            'end_date' => null
        ]);
    } else {
        // Use custom date range if no quick filter is selected
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
    }

    // Sorting - NEW CODE
    $sort = $request->get('sort', 'created_at');
    $direction = $request->get('direction', 'desc');
    
    $allowedSorts = ['id', 'created_at', 'total_refund_amount'];
    if (in_array($sort, $allowedSorts)) {
        $query->orderBy($sort, $direction);
    } else {
        $query->orderBy('created_at', 'desc');
    }
    
    $returns = $query->paginate(10);
    
    // Calculate total refunded amount for display (with same filters)
    $totalQuery = ProductReturn::query();
    
        // Apply search filter to total 
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            preg_match_all('/\d+/', $search, $matches);
            $saleId = $matches[0][0] ?? null;
            
            if ($saleId) {
                $totalQuery->whereHas('sale', function($q) use ($saleId) {
                    $q->where('id', $saleId);
                });
            } else {
                $totalQuery->whereRaw('1 = 0');
            }
        }
    
    // Apply return reason filter to total
    if ($request->has('return_reason') && $request->return_reason != '') {
        $totalQuery->where('return_reason', $request->return_reason);
    }
    
    // Apply date filter to total
    if ($dateFilter) {
        $today = Carbon::today();
        
        switch ($dateFilter) {
            case 'today':
                $totalQuery->whereDate('created_at', $today);
                break;
                
            case 'this_week':
                $totalQuery->whereBetween('created_at', [
                    $today->copy()->startOfWeek(),
                    $today->copy()->endOfWeek()
                ]);
                break;
                
            case 'this_month':
                $totalQuery->whereBetween('created_at', [
                    $today->copy()->startOfMonth(),
                    $today->copy()->endOfMonth()
                ]);
                break;
                
            case 'this_year':
                $totalQuery->whereBetween('created_at', [
                    $today->copy()->startOfYear(),
                    $today->copy()->endOfYear()
                ]);
                break;
        }
    } else {
        // Use custom date range if no quick filter is selected
        if ($request->has('start_date') && $request->start_date != '') {
            $totalQuery->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date != '') {
            $totalQuery->whereDate('created_at', '<=', $request->end_date);
        }
    }
    
    $totalRefunded = $totalQuery->sum('total_refund_amount');

    return view('returns.index', compact('returns', 'totalRefunded', 'sort', 'direction'));
}

    // Add show method for viewing return details
    public function show($id)
    {
        $return = ProductReturn::with([
            'sale', 
            'user', 
            'refundPayment',
            'returnItems.product'
        ])->findOrFail($id);

        return response()->json($return);
    }

    public function create()
    {
        $recentSales = Sale::with('items')->latest()->take(5)->get();
        return view('returns.create');
    }

    public function getSaleDetails($saleId)
    {
        try {
            // Make sure to load the items relationship with product
            $sale = Sale::with(['items.product'])->findOrFail($saleId);

            // Check if sale is within 7 days
            $saleDate = Carbon::parse($sale->sale_date);
            $sevenDaysAgo = Carbon::now()->subDays(7);
            
            if ($saleDate->lt($sevenDaysAgo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Returns are only accepted within 7 days of purchase. This sale is too old.'
                ]);
            }

            // Check if this sale already has returns
            $existingReturns = ProductReturn::where('sale_id', $saleId)->get();
            $alreadyReturnedItems = [];
            
            if ($existingReturns->count() > 0) {
                $returnIds = $existingReturns->pluck('id');
                $alreadyReturnedItems = ReturnItem::whereIn('product_return_id', $returnIds)
                    ->with('saleItem')
                    ->get()
                    ->groupBy('sale_item_id')
                    ->map(function ($items) {
                        return $items->sum('quantity_returned');
                    });
            }

            // Check if items exist and is not null
            if (!$sale->items) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items found for this sale.'
                ]);
            }

            $saleItems = $sale->items->map(function ($item) use ($alreadyReturnedItems) {
                // Also check if product relationship is loaded
                if (!$item->product) {
                    return null;
                }

                $maxReturnable = $item->quantity_sold;
                
                if (isset($alreadyReturnedItems[$item->id])) {
                    $maxReturnable = $item->quantity_sold - $alreadyReturnedItems[$item->id];
                }

                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'quantity_sold' => $item->quantity_sold,
                    'unit_price' => $item->unit_price,
                    'max_returnable' => max(0, $maxReturnable),
                    'already_returned' => $alreadyReturnedItems[$item->id] ?? 0
                ];
            })->filter(); // Remove any null entries

            // Check if we have any valid sale items
            if ($saleItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid items found for this sale.'
                ]);
            }

            return response()->json([
                'success' => true,
                'sale' => [
                    'id' => $sale->id,
                    'sale_date' => $sale->sale_date,
                    'customer_name' => $sale->customer_name,
                    'customer_contact' => $sale->customer_contact,
                    'total_amount' => $sale->items->sum(function ($item) {
                        return $item->quantity_sold * $item->unit_price;
                    }),
                    'items' => $saleItems
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sale not found or error retrieving sale details: ' . $e->getMessage()
            ]);
        }
    }

   public function store(Request $request)
    {
        // Debug: Log what's coming in
        Log::info('Return Store Request Data:', $request->all());
        
        // First, filter out items with quantity 0
        $items = $request->input('items', []);
        $filteredItems = array_filter($items, function($item) {
            return ($item['quantity'] ?? 0) > 0;
        });
        
        // Replace the items in the request
        $request->merge(['items' => $filteredItems]);
        
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'return_reason' => 'required|in:Defective,Wrong Item,Customer Change Mind,Other',
            'notes' => 'nullable|string',
            'refund_method' => 'required|in:Cash,GCash,Card',
            'reference_no' => 'required_if:refund_method,GCash,Card|nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition' => 'required|in:resaleable,damaged',
            'items.*.refund_amount' => 'required|numeric|min:0'
        ]);
        
        Log::info('Validated Data:', $validated);
        
        try {
            DB::beginTransaction();

            // Get sale and verify it's within 7 days
            $sale = Sale::findOrFail($validated['sale_id']);
            $saleDate = Carbon::parse($sale->sale_date);
            $sevenDaysAgo = Carbon::now()->subDays(7);
            
            if ($saleDate->lt($sevenDaysAgo)) {
                return back()->withErrors(['sale_id' => 'Returns are only accepted within 7 days of purchase.']);
            }

            // Calculate total refund amount
            $totalRefundAmount = collect($validated['items'])->sum('refund_amount');

            // Create negative payment record for refund
            $refundPayment = Payment::create([
                'sale_id' => $sale->id,
                'payment_method' => $validated['refund_method'],
                'amount_tendered' => -$totalRefundAmount,
                'change_given' => 0,
                'reference_no' => $validated['reference_no'] ?? null,
                'payment_date' => now()
            ]);

            // Create return header
            $productReturn = ProductReturn::create([
                'sale_id' => $sale->id,
                'user_id' => session('user_id'), 
                'refund_payment_id' => $refundPayment->id,
                'total_refund_amount' => $totalRefundAmount,
                'return_reason' => $validated['return_reason'],
                'notes' => $validated['notes']
            ]);

            // Create return items and update inventory
            foreach ($validated['items'] as $itemData) {
                $saleItem = SaleItem::find($itemData['sale_item_id']);
                
                $returnItem = ReturnItem::create([
                    'product_return_id' => $productReturn->id,
                    'product_id' => $saleItem->product_id,
                    'sale_item_id' => $saleItem->id,
                    'quantity_returned' => $itemData['quantity'],
                    'refunded_price_per_unit' => $itemData['refund_amount'] / $itemData['quantity'],
                    'total_line_refund' => $itemData['refund_amount'],
                    'inventory_adjusted' => $itemData['condition'] === 'resaleable'
                ]);

                // Update inventory if item is resaleable
                if ($itemData['condition'] === 'resaleable') {
                    $product = Product::find($saleItem->product_id);
                    $product->quantity_in_stock += $itemData['quantity'];
                    $product->save();
                }
            }

            DB::commit();

            return redirect()->route('returns.index')
                ->with('success', 'Return processed successfully. Total refund: $' . number_format($totalRefundAmount, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Return processing failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->withErrors(['error' => 'Failed to process return: ' . $e->getMessage()]);
        }
    }

    // You can add these methods later if needed:
    
    // public function show($id)
    // {
    //     // Show individual return details
    // }
    
    // public function edit($id)
    // {
    //     // Edit return (if needed)
    // }
    
    // public function update(Request $request, $id)
    // {
    //     // Update return
    // }
    
    // public function destroy($id)
    // {
    //     // Delete return
    // }
}