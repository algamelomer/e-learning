<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // User Management
    public function getUsers()
    {
        return User::with('roles')->get();
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'roles' => 'required|array'
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email']
        ]);

        $user->roles()->sync($validated['roles']);

        return response()->json(['message' => 'User updated successfully']);
    }

    public function deleteUser(User $user)
    {
        // Prevent deletion of users with admin role
        if ($user->roles()->where('name', 'admin')->exists()) {
            return response()->json(['message' => 'Cannot delete admin users'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    // Role Management
    public function getRoles()
    {
        return Role::withCount('users')->get();
    }

    public function createRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'required|string'
        ]);

        $role = Role::create($validated);
        return response()->json($role, 201);
    }

    public function updateRole(Request $request, Role $role)
    {
        // Prevent updating core roles
        if (in_array($role->name, ['admin', 'user'])) {
            return response()->json(['message' => 'Cannot modify core roles'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'required|string'
        ]);

        $role->update($validated);
        return response()->json(['message' => 'Role updated successfully']);
    }

    public function deleteRole(Role $role)
    {
        // Prevent deletion of core roles
        if (in_array($role->name, ['admin', 'user'])) {
            return response()->json(['message' => 'Cannot delete core roles'], 403);
        }

        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }
}
