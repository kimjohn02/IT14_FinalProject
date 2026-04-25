<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $showArchived = $request->has('archived');
        
        $query = User::with('disabledBy');

        if ($showArchived) {
            $query->archived();
        } else {
            $query->active();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('f_name', 'like', "%{$search}%")
                  ->orWhere('m_name', 'like', "%{$search}%")
                  ->orWhere('l_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contactNo', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'asc')->paginate(10);        

        $roles = ['Administrator', 'Cashier'];

        return view('users.index', compact('users', 'roles', 'showArchived'));
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
                'username' => 'required|string|max:50|unique:users',
                'f_name' => 'required|string|max:100',
                'm_name' => 'nullable|string|max:100',
                'l_name' => 'required|string|max:100',
                'contactNo' => 'nullable|string|max:50',
                'role' => 'required|in:Administrator,Cashier',
                'email' => 'required|string|email|max:255|unique:users',
            ]);

            // TEMPORARY PASSWORD
            $tempPassword = strtoupper(Str::random(2)) . random_int(1000, 9999);

            $user = User::create([
                'username' => $request->username,
                'f_name' => ucwords(strtolower($request->f_name)),
                'm_name' => $request->m_name ? ucwords(strtolower($request->m_name)) : null,
                'l_name' => ucwords(strtolower($request->l_name)),
                'contactNo' => $request->contactNo,
                'role' => $request->role,
                'email' => $request->email,
                'password' => Hash::make($tempPassword),
                'password_changed' => false,
                'is_active' => true,
            ]);

            return redirect()->route('users.index')->with('temp_password', $tempPassword)->with('new_user_name', $user->full_name) 
            ->with('new_user_username', $user->username)->with('success', 'User added successfully!');
            
        } catch (Exception $e) {
            return redirect()->route('users.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('disabledBy');
        return response()->json($user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            $request->validate([
                'username' => 'required|string|max:50|unique:users,username,' . $user->id,
                'f_name' => 'required|string|max:100',
                'm_name' => 'nullable|string|max:100',
                'l_name' => 'required|string|max:100',
                'contactNo' => 'nullable|string|max:50',
                'role' => 'required|in:Administrator,Cashier',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|confirmed|min:8',
            ]);

            if ($user->id == session('user_id') && $request->role !== $user->role) {
                return redirect()->route('users.index')->with('error', 'You cannot change your own role.');
            }

            $updateData = [
                'username' => $request->username,
                'f_name' => ucwords(strtolower($request->f_name)),
                'm_name' => $request->m_name ? ucwords(strtolower($request->m_name)) : null,
                'l_name' => ucwords(strtolower($request->l_name)),
                'contactNo' => $request->contactNo,
                'role' => $request->role,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return redirect()->route('users.index')->with('success', 'User updated successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('users.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function archive(User $user)
    {
        try {
            $currentUserId = session('user_id');
            
            if ($currentUserId === $user->id) {
                return redirect()->route('users.index')->with('error', 'You cannot archive your own account.');
            }

            $user->update([
                'is_active' => false,
                'date_disabled' => now(),
                'disabled_by_user_id' => $currentUserId,
            ]);

            return redirect()->route('users.index')->with('success', 'User archived successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('users.index')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function restore(User $user)
    {
        try {
            $user->update([
                'is_active' => true,
                'date_disabled' => null,
                'disabled_by_user_id' => null,
            ]);

            return redirect()->route('users.index', ['archived' => true])->with('success', 'User restored successfully.');
            
        } catch (Exception $e) {
            return redirect()->route('users.index', ['archived' => true])->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function resetPassword(Request $request, User $user)
    {
        try {
            $request->validate([
                'password' => ['required', 'confirmed', 'min:8'],
            ]);

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return redirect()->route('users.index')
                ->with('success', 'Password for ' . $user->full_name . ' has been successfully reset.');

        } catch (Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }
}