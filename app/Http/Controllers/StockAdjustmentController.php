<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockAdjustment::with([
            'processedBy' => function($q) {
                $q->withDefault([
                    'f_name' => 'Unknown',
                    'l_name' => 'User'
                ]);
            },
            'items.product'
        ]);
    
       // Search
        if ($request->filled('search')) {
            $query->whereHas('items.product', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }
    
        // Filter by adjustment type
        if ($request->filled('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }
    
        // Date filter logic
        $dateFilter = $request->input('date_filter');
        
        if ($dateFilter) {
            $today = Carbon::today();
            
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('adjustment_date', $today);
                    break;
                    
                case 'this_week':
                    $query->whereBetween('adjustment_date', [
                        $today->copy()->startOfWeek(),
                        $today->copy()->endOfWeek()
                    ]);
                    break;
                    
                case 'this_month':
                    $query->whereBetween('adjustment_date', [
                        $today->copy()->startOfMonth(),
                        $today->copy()->endOfMonth()
                    ]);
                    break;
                    
                case 'this_year':
                    $query->whereBetween('adjustment_date', [
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
            if ($request->filled('start_date')) {
                $query->whereDate('adjustment_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('adjustment_date', '<=', $request->end_date);
            }
        }
    
        // Sorting
        $sort = $request->get('sort', 'adjustment_date');
        $direction = $request->get('direction', 'desc');
        
        $allowedSorts = ['id', 'adjustment_date'];
        
        if (in_array($sort, $allowedSorts)) {
            // Direct column sorts
            $query->orderBy($sort, $direction);
        } elseif ($sort == 'net_qty_change') {
            // Sort by calculated net quantity change - FIXED VERSION
            $query->addSelect(['net_qty_change' => function($q) {
                $q->selectRaw('COALESCE(SUM(quantity_change), 0)')
                  ->from('stock_adjustment_items')
                  ->whereColumn('stock_adjustment_id', 'stock_adjustments.id');
            }])->orderBy('net_qty_change', $direction);
        } elseif ($sort == 'financial_impact') {
            // Sort by calculated financial impact - FIXED VERSION
            $query->addSelect(['financial_impact' => function($q) {
                $q->selectRaw('COALESCE(SUM(quantity_change * unit_cost_at_adjustment), 0)')
                  ->from('stock_adjustment_items')
                  ->whereColumn('stock_adjustment_id', 'stock_adjustments.id');
            }])->orderBy('financial_impact', $direction);
        } else {
            // Default sort
            $query->orderBy('adjustment_date', 'desc');
        }
    
        $stockAdjustments = $query->paginate(10);
        $adjustmentTypes = [
            'Physical Count',
            'Damage/Scrap',
            'Internal Use',
            'Error Correction',
            'Found Stock'
        ];
    
        return view('adjustments.index', compact('stockAdjustments', 'adjustmentTypes', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::active()->get();
        return view('adjustments.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::info('StockAdjustment Store Request:', $request->all());
            
            // Validate the request
            $validated = $request->validate([
                'adjustment_date' => 'required|date',
                'adjustment_type' => 'required|in:Physical Count,Damage/Scrap,Internal Use,Error Correction,Found Stock',
                'reason_notes' => 'required|string|max:1000',
                'processed_by_user_id' => 'required|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity_change' => 'required|integer',
                'items.*.unit_cost_at_adjustment' => 'required|numeric|min:0',
            ]);

            Log::info('Validated data:', $validated);

            // Use database transaction to ensure data consistency
            DB::beginTransaction();

            // Step 1: Create stock adjustment header
            $stockAdjustment = StockAdjustment::create([
                'adjustment_date' => $validated['adjustment_date'],
                'adjustment_type' => $validated['adjustment_type'],
                'reason_notes' => $validated['reason_notes'],
                'processed_by_user_id' => $validated['processed_by_user_id'],
            ]);

            Log::info('StockAdjustment header created:', $stockAdjustment->toArray());

            $totalFinancialImpact = 0;

            // Step 2: Process adjustment items
            foreach ($validated['items'] as $itemData) {
                Log::info('Processing adjustment item:', $itemData);
                
                // Create stock adjustment item
                $adjustmentItem = StockAdjustmentItem::create([
                    'stock_adjustment_id' => $stockAdjustment->id,
                    'product_id' => $itemData['product_id'],
                    'quantity_change' => $itemData['quantity_change'],
                    'unit_cost_at_adjustment' => $itemData['unit_cost_at_adjustment'],
                ]);

                Log::info('StockAdjustment item created:', $adjustmentItem->toArray());

                // Step 3: Update product stock
                $product = Product::find($itemData['product_id']);
                $oldStock = $product->quantity_in_stock;
                
                // Update the stock level
                $product->increment('quantity_in_stock', $itemData['quantity_change']);
                
                $newStock = $product->quantity_in_stock;

                Log::info("Product stock updated - Product ID: {$product->id}, Old: {$oldStock}, Change: {$itemData['quantity_change']}, New: {$newStock}");

                // Calculate financial impact for this item
                $itemFinancialImpact = $itemData['quantity_change'] * $itemData['unit_cost_at_adjustment'];
                $totalFinancialImpact += $itemFinancialImpact;
            }

            Log::info('Total financial impact: ' . $totalFinancialImpact);

            // Commit the transaction
            DB::commit();

            Log::info('StockAdjustment processed successfully');

            return response()->json([
                'success' => true,
                'message' => 'Stock Adjustment posted successfully',
                'data' => [
                    'adjustment_id' => $stockAdjustment->id,
                    'financial_impact' => $totalFinancialImpact
                ]
            ]);

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            Log::error('StockAdjustment Store Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error processing stock adjustment: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $stockAdjustment = StockAdjustment::with([
            'processedBy',
            'items.product'
        ])->findOrFail($id);

        return response()->json($stockAdjustment);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Typically stock adjustments are not editable once posted for audit integrity
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Typically stock adjustments are not editable once posted for audit integrity
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Typically stock adjustments are not deletable once posted for audit integrity
        abort(404);
    }
}