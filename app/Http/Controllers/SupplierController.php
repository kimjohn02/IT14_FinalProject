<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $showArchived = $request->has('archived');
        
        $query = Supplier::with(['disabledBy']);
        $query = Supplier::with(['products' => function($q) {
            $q->where('is_active', true); // Only count active products
        }]);

        if ($showArchived) {
            $query->archived();
        } else {
            $query->active();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('supplier_name', 'like', '%' . $search . '%')
                  ->orWhere('contactNO', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%');
            });
        }

        $suppliers = $query->orderBy('id', 'asc')->paginate(10);

        return view('suppliers.index', compact('suppliers', 'showArchived'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'supplier_name' => 'required|string|max:150|unique:suppliers,supplier_name',
                'contactNO' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:255',
            ]);

            $supplier = Supplier::create([
                'supplier_name' => $request->supplier_name,
                'contactNO' => $request->contactNO,
                'address' => $request->address,
                'is_active' => true,
            ]);
        
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'supplier' => [
                        'id' => $supplier->id,
                        'supplier_name' => $supplier->supplier_name
                    ]
                ]);
            }

            return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully.');
            
        } catch (Exception $e) {
            // Return JSON error for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Error: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->route('suppliers.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load(['disabledBy']);
        return response()->json($supplier);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return response()->json($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        try {
            $request->validate([
                'supplier_name' => 'required|string|max:150|unique:suppliers,supplier_name,' . $supplier->id,
                'contactNO' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:255',
            ]);

            $supplier->update([
                'supplier_name' => $request->supplier_name,
                'contactNO' => $request->contactNO,
                'address' => $request->address,
            ]);

            return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('suppliers.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function archive(Request $request, Supplier $supplier)
    {
        // Check if supplier is a default supplier
        if ($supplier->isDefaultSupplier()) {
            return redirect()->back()->with('error', 'Cannot archive supplier that is set as default supplier for products.');
        }

        $request->validate([
            'archive_reason' => 'nullable|string|max:255',
        ]);
    
        $currentUserId = session('user_id');
      
        $supplier->update([
            'is_active' => false,
            'date_disabled' => now(),
            'disabled_by_user_id' => $currentUserId,
            'archive_reason' => $request->archive_reason, 
        ]);
    
        return redirect()->back()->with('success', 'Supplier archived successfully.');
    }

    public function restore(Supplier $supplier)
    {
        try {
            $supplier->update([
                'is_active' => true,
                'date_disabled' => null,
                'disabled_by_user_id' => null,
            ]);

            return redirect()->route('suppliers.index', ['archived' => true])->with('success', 'Supplier restored successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('suppliers.index', ['archived' => true])->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function quickAdd(Request $request)
{
    $request->validate([
        'supplier_name' => 'required|string|max:150',
        'contactNO' => 'nullable|string|max:50',
        'address' => 'nullable|string|max:255',
    ]);

    $supplier = Supplier::create($request->only(['supplier_name', 'contactNO', 'address']));

    return response()->json([
        'success' => true,
        'supplier' => [
            'id' => $supplier->id,
            'supplier_name' => $supplier->supplier_name
        ]
    ]);
}
}