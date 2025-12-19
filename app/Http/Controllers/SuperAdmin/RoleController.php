<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index(Request $request)
    {
        $query = Role::withCount(['permissions', 'users']);

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $roles = $query->orderBy('name')->paginate(20);

        // Statistics
        $stats = [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_users' => \App\Models\User::count(),
        ];

        return view('superadmin.roles.index', compact('roles', 'stats'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            // Group by module (e.g., "user.create" -> "user")
            return explode('.', $permission->name)[0] ?? 'general';
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
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create role
        $role = Role::create([
            'name' => strtolower(str_replace(' ', '_', $request->name)),
            'guard_name' => 'web',
        ]);

        // Assign permissions if selected
        if ($request->filled('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->pluck('name');
            $role->givePermissionTo($permissions);
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

        // Get permissions grouped by module
        $groupedPermissions = $role->permissions->groupBy(function($permission) {
            return explode('.', $permission->name)[0] ?? 'general';
        });

        // Recent activity related to this role
        $recentActivity = ActivityLog::where('model_type', 'Role')
            ->where('model_id', $role->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('superadmin.roles.show', compact('role', 'groupedPermissions', 'recentActivity'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit(Role $role)
    {
        // Prevent editing system roles
        $systemRoles = ['superadmin', 'admin', 'teacher', 'parent'];
        if (in_array($role->name, $systemRoles)) {
            return redirect()->route('superadmin.roles.index')
                ->with('warning', 'System roles cannot be edited. You can only manage their permissions.');
        }

        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            return explode('.', $permission->name)[0] ?? 'general';
        });

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('superadmin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, Role $role)
    {
        // Prevent editing system roles
        $systemRoles = ['superadmin', 'admin', 'teacher', 'parent'];
        if (in_array($role->name, $systemRoles)) {
            return redirect()->route('superadmin.roles.index')
                ->with('error', 'System roles cannot be edited.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update role
        $role->update([
            'name' => strtolower(str_replace(' ', '_', $request->name)),
        ]);

        // Sync permissions
        if ($request->filled('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->pluck('name');
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

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
        // Prevent deleting system roles
        $systemRoles = ['superadmin', 'admin', 'teacher', 'parent'];
        if (in_array($role->name, $systemRoles)) {
            return redirect()->route('superadmin.roles.index')
                ->with('error', 'System roles cannot be deleted.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('superadmin.roles.index')
                ->with('error', 'Cannot delete role that has assigned users. Please reassign users first.');
        }

        $roleName = $role->name;
        $roleId = $role->id;

        // Delete role
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

    /**
     * Update role permissions (AJAX)
     */
    public function updatePermissions(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $permissions = Permission::whereIn('id', $request->permissions)->pluck('name');
        $role->syncPermissions($permissions);

        // Log activity
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated_role_permissions',
            'model_type' => 'Role',
            'model_id' => $role->id,
            'description' => "Updated permissions for role: {$role->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully!'
        ]);
    }
}