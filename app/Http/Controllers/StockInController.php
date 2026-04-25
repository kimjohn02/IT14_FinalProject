<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StockInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $query = StockIn::with([
        'receivedBy' => function($q) {
            $q->withDefault([
                'f_name' => 'Unknown',
                'l_name' => 'User'
            ]);
        },
        'items.product',
        'items.supplier'
    ]);

    // Search
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('reference_no', 'like', '%' . $request->search . '%')
            ->orWhereHas('items.product', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            })
            ->orWhereHas('items.supplier', function($q) use ($request) { 
                $q->where('supplier_name', 'like', '%' . $request->search . '%');
            });
        });
    }

    // Date filter logic - NEW CODE
    $dateFilter = $request->input('date_filter');
    
    if ($dateFilter) {
        $today = Carbon::today();
        
        switch ($dateFilter) {
            case 'today':
                $query->whereDate('stock_in_date', $today);
                break;
                
            case 'this_week':
                $query->whereBetween('stock_in_date', [
                    $today->copy()->startOfWeek(),
                    $today->copy()->endOfWeek()
                ]);
                break;
                
            case 'this_month':
                $query->whereBetween('stock_in_date', [
                    $today->copy()->startOfMonth(),
                    $today->copy()->endOfMonth()
                ]);
                break;
                
            case 'this_year':
                $query->whereBetween('stock_in_date', [
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
            $query->whereDate('stock_in_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('stock_in_date', '<=', $request->end_date);
        }
    }

    // Sorting
    $sort = $request->get('sort', 'stock_in_date');
    $direction = $request->get('direction', 'desc');
    
    $allowedSorts = ['id', 'stock_in_date', 'reference_no', 'created_at'];
    if (in_array($sort, $allowedSorts)) {
        $query->orderBy($sort, $direction);
    } else {
        $query->orderBy('stock_in_date', 'desc');
    }

    $stockIns = $query->paginate(10);

    return view('stock-in.index', compact('stockIns', 'sort', 'direction'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $suppliers = Supplier::active()->get();
        $products = Product::active()->with('defaultSupplier')->get(); 

        $selectedProduct = null;
        if ($request->has('product_id')) {
            $selectedProduct = Product::with('defaultSupplier')->find($request->product_id);
        }
        
        return view('stock-in.create', compact('suppliers', 'products', 'selectedProduct'));    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::info('StockIn Store Request:', $request->all());
            
            // Validate the request structure
            $validated = $request->validate([
                'reference_no' => 'required|string|max:255',
                'stock_in_date' => 'required|date',
                'received_by_user_id' => 'required|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.supplier_id' => 'required|exists:suppliers,id',
                'items.*.quantity_received' => 'required|integer|min:1',
                'items.*.actual_unit_cost' => 'required|numeric|min:0',
                'items.*.retail_price' => 'required|numeric|min:0',
            ]);
    
            Log::info('Validated data:', $validated);
    
                // Create stock in record
                $stockIn = StockIn::create([
                    'reference_no' => $validated['reference_no'],
                    'stock_in_date' => $validated['stock_in_date'],
                    'received_by_user_id' => $validated['received_by_user_id'],
                    'status' => 'completed',
                ]);
                
                // Process items
                foreach ($validated['items'] as $item) {
                    StockInItem::create([
                        'stock_in_id' => $stockIn->id,
                        'product_id' => $item['product_id'],
                        'supplier_id' => $item['supplier_id'],
                        'quantity_received' => $item['quantity_received'],
                        'actual_unit_cost' => $item['actual_unit_cost'],
                    ]);
                
                    // Update product stock
                    $product = Product::find($item['product_id']);
                    $product->increment('quantity_in_stock', $item['quantity_received']);
                    $product->update(['latest_unit_cost' => $item['actual_unit_cost']]);
                
                    // Update retail price entry
                    \App\Models\ProductPrice::create([
                        'product_id' => $item['product_id'],
                        'retail_price' => $item['retail_price'],
                        'stock_in_id' => $stockIn->id,
                        'updated_by_user_id' => $validated['received_by_user_id']
                    ]);
                
            }
    
            Log::info('StockIn processed successfully');
    
            return response()->json([
                'success' => true,
                'message' => 'Stock In processed successfully',
                'data' => $validated
            ]);
    
        } catch (\Exception $e) {
            Log::error('StockIn Store Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
    
            return response()->json([
                'success' => false,
                'message' => 'Error processing stock in: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockIn $stockIn)
    {
        $stockIn->load(['receivedBy', 'items.supplier', 'items.product']);
        return response()->json($stockIn);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}