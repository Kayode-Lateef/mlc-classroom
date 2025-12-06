<?php
// FILE: app/Http/Controllers/SuperAdmin/RoleController.php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index()
    {
        $roles = Role::withCount('permissions', 'users')->get();
        
        return view('superadmin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode(' ', $permission->name)[1] ?? 'other';
        });
        
        return view('superadmin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create role
        $role = Role::create(['name' => $request->name]);

        // Assign permissions
        if ($request->has('permissions')) {
            $role->givePermissionTo($request->permissions);
        }

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'created_role',
            'model_type' => 'Role',
            'model_id' => $role->id,
            'description' => "Created role: {$role->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.roles.index')
            ->with('success', 'Role created successfully!');
    }

    /**
     * Display the specified role
     */
    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        
        return view('superadmin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        // Prevent editing core roles
        if (in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent'])) {
            return redirect()->route('superadmin.roles.index')
                ->with('warning', 'Core system roles cannot be edited.');
        }

        $permissions = Permission::all()->groupBy(function($permission) {
            return explode(' ', $permission->name)[1] ?? 'other';
        });
        
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        
        return view('superadmin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        // Prevent editing core roles
        if (in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent'])) {
            return redirect()->route('superadmin.roles.index')
                ->with('error', 'Core system roles cannot be modified.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update role name
        $role->update(['name' => $request->name]);

        // Sync permissions
        $role->syncPermissions($request->permissions ?? []);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated_role',
            'model_type' => 'Role',
            'model_id' => $role->id,
            'description' => "Updated role: {$role->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.roles.index')
            ->with('success', 'Role updated successfully!');
    }

    /**
     * Remove the specified role
     */
    public function destroy(Role $role)
    {
        // Prevent deleting core roles
        if (in_array($role->name, ['superadmin', 'admin', 'teacher', 'parent'])) {
            return redirect()->route('superadmin.roles.index')
                ->with('error', 'Core system roles cannot be deleted.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('superadmin.roles.index')
                ->with('error', 'Cannot delete role that has assigned users.');
        }

        $roleName = $role->name;
        $roleId = $role->id;
        $role->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted_role',
            'model_type' => 'Role',
            'model_id' => $roleId,
            'description' => "Deleted role: {$roleName}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('superadmin.roles.index')
            ->with('success', 'Role deleted successfully!');
    }
}
