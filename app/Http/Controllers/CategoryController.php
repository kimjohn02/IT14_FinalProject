<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Exception;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::withCount('products'); 

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('sku_prefix', 'like', '%' . $search . '%');
            });
        }

        $categories = $query->orderBy('id', 'asc')->paginate(10);        
        return view('categories.index', compact('categories'));
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
        try {
            $request->validate([
                'name' => 'required|string|max:50|unique:categories,name',
                'description' => 'nullable|string|max:255',
                'sku_prefix' => 'required|string|max:10|unique:categories,sku_prefix',
            ]);

            $capitalizedName = ucwords(strtolower($request->name));

            Category::create([
                'name' => $capitalizedName,
                'description' => $request->description,
                'sku_prefix' => strtoupper($request->sku_prefix),
            ]);
    
            return redirect()->route('categories.index')->with('success', 'Category added successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('categories.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return response()->json($category);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:50|unique:categories,name,' . $category->id,
                'description' => 'nullable|string|max:255',
            ]);

            $capitalizedName = ucwords(strtolower($request->name));

            $category->update([
                'name' => $capitalizedName,
                'description' => $request->description,
            ]);


            return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('categories.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            if ($category->products()->exists()) {
                return redirect()->route('categories.index')->with('error', 'Cannot delete category. There are products associated with this category.');
            }

            $category->delete();

            return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('categories.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }
}