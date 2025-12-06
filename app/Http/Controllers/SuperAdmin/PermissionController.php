<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions
     */
    public function index()
    {
        // Group permissions by category for better organization
        $permissions = Permission::withCount('roles')->get()->groupBy(function($permission) {
            $parts = explode(' ', $permission->name);
            return $parts[1] ?? 'other';
        });
        
        return view('superadmin.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new permission
     */
    public function create()
    {
        // Get existing permission categories for suggestions
        $categories = Permission::all()
            ->map(function($permission) {
                $parts = explode(' ', $permission->name);
                return $parts[1] ?? 'other';
            })
            ->unique()
            ->values();
        
        return view('superadmin.permissions.create', compact('categories'));
    }

    /**
     * Store a newly created permission
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name',
            'description' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create permission
        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'created_permission',
            'model_type' => 'Permission',
            'model_id' => $permission->id,
            'description' => "Created permission: {$permission->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.permissions.index')
            ->with('success', 'Permission created successfully!');
    }

    /**
     * Display the specified permission
     */
    public function show(Permission $permission)
    {
        $permission->load('roles');
        
        // Get users who have this permission (directly or through roles)
        $rolesWithPermission = $permission->roles;
        
        return view('superadmin.permissions.show', compact('permission', 'rolesWithPermission'));
    }

    /**
     * Show the form for editing the specified permission
     */
    public function edit(Permission $permission)
    {
        // Get existing permission categories
        $categories = Permission::all()
            ->map(function($perm) {
                $parts = explode(' ', $perm->name);
                return $parts[1] ?? 'other';
            })
            ->unique()
            ->values();
        
        $roles = Role::all();
        $permissionRoles = $permission->roles->pluck('id')->toArray();
        
        return view('superadmin.permissions.edit', compact('permission', 'categories', 'roles', 'permissionRoles'));
    }

    /**
     * Update the specified permission
     */
    public function update(Request $request, Permission $permission)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update permission name
        $permission->update(['name' => $request->name]);

        // Sync roles if provided
        if ($request->has('roles')) {
            $roles = Role::whereIn('id', $request->roles)->get();
            foreach ($roles as $role) {
                if (!$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
            
            // Remove permission from roles not in the request
            $rolesToRemove = Role::whereNotIn('id', $request->roles)->get();
            foreach ($rolesToRemove as $role) {
                if ($role->hasPermissionTo($permission)) {
                    $role->revokePermissionTo($permission);
                }
            }
        }

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated_permission',
            'model_type' => 'Permission',
            'model_id' => $permission->id,
            'description' => "Updated permission: {$permission->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.permissions.index')
            ->with('success', 'Permission updated successfully!');
    }

    /**
     * Remove the specified permission
     */
    public function destroy(Permission $permission)
    {
        // Check if permission is assigned to any roles
        if ($permission->roles()->count() > 0) {
            return redirect()->route('superadmin.permissions.index')
                ->with('error', 'Cannot delete permission that is assigned to roles. Remove from roles first.');
        }

        // Prevent deletion of core permissions
        $corePermissions = [
            'manage users', 'manage students', 'manage classes', 
            'manage schedules', 'mark attendance', 'view attendance'
        ];
        
        if (in_array($permission->name, $corePermissions)) {
            return redirect()->route('superadmin.permissions.index')
                ->with('error', 'Core system permissions cannot be deleted.');
        }

        $permissionName = $permission->name;
        $permissionId = $permission->id;
        $permission->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted_permission',
            'model_type' => 'Permission',
            'model_id' => $permissionId,
            'description' => "Deleted permission: {$permissionName}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('superadmin.permissions.index')
            ->with('success', 'Permission deleted successfully!');
    }

    /**
     * Bulk assign permissions to a role
     */
    public function bulkAssign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = Role::findById($request->role_id);
        $permissions = Permission::whereIn('id', $request->permissions)->get();
        
        $role->givePermissionTo($permissions);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'bulk_assigned_permissions',
            'model_type' => 'Role',
            'model_id' => $role->id,
            'description' => "Bulk assigned " . count($permissions) . " permissions to role: {$role->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned successfully!'
        ]);
    }
}