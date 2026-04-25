<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $showArchived = $request->has('archived');
        
        $query = Product::with(['category', 'disabledBy', 'defaultSupplier']);

        if ($showArchived) {
            $query->archived();
        } else {
            $query->active();
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Category Filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Stock Filter - NEW
        if ($request->filled('stock_filter') && !$showArchived) {
            switch ($request->stock_filter) {
                case 'okay_stock':
                    $query->where('quantity_in_stock', '>', DB::raw('reorder_level'));
                    break;
                case 'low_stock':
                    $query->where('quantity_in_stock', '>', 0)
                        ->where('quantity_in_stock', '<=', DB::raw('reorder_level'));
                    break;
                case 'out_of_stock':
                    $query->where('quantity_in_stock', 0);
                    break;
            }
        }

        // Sorting
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');
        
        $allowedSorts = ['name', 'sku', 'quantity_in_stock', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        $products = $query->paginate(10);
        $categories = Category::all();
        $suppliers = Supplier::active()->get();

        return view('products.index', compact('products', 'categories', 'suppliers', 'showArchived', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $suppliers = Supplier::active()->get();
        return view('products.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'sku_suffix' => 'required|integer|min:1|max:99999|digits_between:1,5', 
                'name' => 'required|string|max:150',
                'model' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:500',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'manufacturer_barcode' => 'nullable|string|max:30|unique:products,manufacturer_barcode',
                'reorder_level' => 'required|integer|min:0',
                'default_supplier_id' => 'required|exists:suppliers,id',
                // Removed suppliers array validation
            ]);
            
            $sku = Product::generateSku($request->category_id, $request->sku_suffix);

            $request->validate([
                'sku_suffix' => 'unique:products,sku',
            ], [
                'sku_suffix.unique' => 'This SKU suffix is already taken for the selected category. Please choose a different number.'
            ]);

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                // Create directory if it doesn't exist
                $directory = public_path('images/products');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Generate unique filename
                $originalName = pathinfo($request->file('image')->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $request->file('image')->getClientOriginalExtension();
                $filename = time() . '_' . $originalName . '_' . uniqid() . '.' . $extension;
                
                // Move file to public directory
                $request->file('image')->move($directory, $filename);
                $imagePath = 'images/products/' . $filename;
            }

            // Create the product with mandatory supplier field
            $product = Product::create([
                'sku' => $sku,
                'name' => ucfirst($request->name),
                'model' => $request->model, 
                'description' => $request->description,
                'category_id' => $request->category_id,
                'image_path' => $imagePath,
                'manufacturer_barcode' => $request->manufacturer_barcode,
                'reorder_level' => $request->reorder_level,
                'default_supplier_id' => $request->default_supplier_id,
                'latest_unit_cost' => null, 
                'is_active' => true,
            ]);

            // Removed supplier attachment logic since we only have default_supplier_id now

            return redirect()->route('products.index')->with('success', 'Product added successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('products.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'disabledBy', 'defaultSupplier']); // Changed from suppliers to defaultSupplier
        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        $suppliers = Supplier::active()->get();
        // Removed loading suppliers relationship
        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:150',
                'model' => 'nullable|string|max:100', 
                'description' => 'nullable|string|max:500',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'manufacturer_barcode' => 'nullable|string|max:30|unique:products,manufacturer_barcode,' . $product->id,
                'reorder_level' => 'required|integer|min:0',
                'default_supplier_id' => 'required|exists:suppliers,id',
                // Removed suppliers array validation
            ]);

            // Handle image upload
            $imagePath = $product->image_path;
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($imagePath && file_exists(public_path($imagePath))) {
                    unlink(public_path($imagePath));
                }
                
                // Create directory if it doesn't exist
                $directory = public_path('images/products');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Generate unique filename
                $originalName = pathinfo($request->file('image')->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $request->file('image')->getClientOriginalExtension();
                $filename = time() . '_' . $originalName . '_' . uniqid() . '.' . $extension;
                
                // Move file to public directory
                $request->file('image')->move($directory, $filename);
                $imagePath = 'images/products/' . $filename;
            }

            if ($request->delete_image == '1') {
                // Delete old image if exists
                if ($imagePath && file_exists(public_path($imagePath))) {
                    unlink(public_path($imagePath));
                }
                $imagePath = null;
            }

            $product->update([
                'name' => ucfirst($request->name),
                'model' => $request->model, 
                'description' => $request->description,
                'category_id' => $request->category_id,
                'image_path' => $imagePath,
                'manufacturer_barcode' => $request->manufacturer_barcode,
                'reorder_level' => $request->reorder_level,
                'default_supplier_id' => $request->default_supplier_id,
            ]);

            // Removed supplier sync logic since we only have default_supplier_id now

            return redirect()->route('products.index')->with('success', 'Product updated successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('products.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function archive(Request $request, Product $product)
    {
        try {
            $currentUserId = session('user_id');

            $request->validate([
                'archive_reason' => 'nullable|string|max:500',
            ]);

            $product->update([
                'is_active' => false,
                'date_disabled' => now(),
                'disabled_by_user_id' => $currentUserId,
                'archive_reason' => $request->archive_reason, 
            ]);

            return redirect()->route('products.index')->with('success', 'Product archived successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('products.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function restore(Product $product)
    {
        try {
            $product->update([
                'is_active' => true,
                'date_disabled' => null,
                'disabled_by_user_id' => null,
            ]);

            return redirect()->route('products.index', ['archived' => true])->with('success', 'Product restored successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('products.index', ['archived' => true])->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function suggestSku($categoryId)
    {
        try {
            $suggestedSuffix = 1;
            
            $latestProduct = Product::where('sku', 'like', Category::find($categoryId)->sku_prefix . '-%')
                ->orderBy('sku', 'desc')
                ->first();

            if ($latestProduct) {
                $prefix = Category::find($categoryId)->sku_prefix;
                $lastSuffix = intval(substr($latestProduct->sku, strlen($prefix) + 1));
                $suggestedSuffix = $lastSuffix + 1;
            }

            return response()->json([
                'suggested_suffix' => $suggestedSuffix
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'suggested_suffix' => 1
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}