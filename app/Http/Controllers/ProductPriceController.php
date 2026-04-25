<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Http\Request;

class ProductPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $showArchived = $request->has('archived');
        
        $query = Product::with(['latestProductPrice', 'latestProductPrice.updatedBy', 'latestProductPrice.stockIn', 'latestStockInItem']);

        if ($showArchived) {
            $query->archived();
        } else {
            $query->active();
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('sku', 'like', '%' . $request->search . '%')
                ->orWhere('manufacturer_barcode', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');
        
        $allowedSorts = ['name', 'retail_price', 'cost_price'];
        if (in_array($sort, $allowedSorts)) {
            if ($sort == 'retail_price') {
                $query->leftJoin('product_prices', function($join) {
                    $join->on('products.id', '=', 'product_prices.product_id')
                        ->whereRaw('product_prices.id = (
                            SELECT id FROM product_prices 
                            WHERE product_id = products.id 
                            ORDER BY created_at DESC 
                            LIMIT 1
                        )');
                })
                ->orderBy('product_prices.retail_price', $direction);
            } elseif ($sort == 'cost_price') {
                $query->leftJoin('stock_in_items', function($join) {
                    $join->on('products.id', '=', 'stock_in_items.product_id')
                        ->whereRaw('stock_in_items.id = (
                            SELECT id FROM stock_in_items 
                            WHERE product_id = products.id 
                            ORDER BY created_at DESC 
                            LIMIT 1
                        )');
                })
                ->orderBy('stock_in_items.actual_unit_cost', $direction);
            } else {
                $query->orderBy($sort, $direction);
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $products = $query->paginate(15);

        return view('product-prices.index', compact('products', 'sort', 'direction', 'showArchived'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductPrice $productPrice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductPrice $productPrice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductPrice $productPrice)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'retail_price' => 'required|numeric|min:0|max:1000000'
        ]);
    
        try {
            ProductPrice::create([
                'product_id' => $request->product_id,
                'retail_price' => $request->retail_price,
                'updated_by_user_id' => session('user_id'),
                'stock_in_id' => null // Manual price update, not from stock in
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Price updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating price: ' . $e->getMessage()
            ], 500);
        }
    }

    public function priceHistory($productId)
    {
        $prices = ProductPrice::with(['updatedBy', 'stockIn'])
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($prices);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductPrice $productPrice)
    {
        //
    }
}
