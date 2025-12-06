<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // User Management
            'manage users',
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Student Management
            'manage students',
            'view students',
            'create students',
            'edit students',
            'delete students',
            
            // Class Management
            'manage classes',
            'view classes',
            'create classes',
            'edit classes',
            'delete classes',
            
            // Schedule Management
            'manage schedules',
            'view schedules',
            'create schedules',
            'edit schedules',
            'delete schedules',
            
            // Attendance
            'mark attendance',
            'view attendance',
            'edit attendance',
            'view own class attendance',
            'view child attendance',
            
            // Homework
            'create homework',
            'view homework',
            'edit homework',
            'delete homework',
            'grade homework',
            'submit homework',
            'view own homework',
            'view child homework',
            
            // Progress Tracking
            'create progress',
            'view progress',
            'edit progress',
            'delete progress',
            'view own class progress',
            'view child progress',
            
            // Learning Resources
            'manage resources',
            'upload resources',
            'view resources',
            'delete resources',
            
            // Reports
            'generate reports',
            'view reports',
            'export reports',
            
            // Settings
            'manage settings',
            'view settings',
            
            // SMS & Notifications
            'send sms',
            'view sms logs',
            'manage notifications',
            
            // Role & Permission Management (Superadmin only)
            'manage roles',
            'manage permissions',
            'assign roles',
            'manage system',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Roles and Assign Permissions

        // SUPERADMIN ROLE - Ultimate Access (includes role/permission management)
        $superadmin = Role::create(['name' => 'superadmin']);
        $superadmin->givePermissionTo(Permission::all());

        // ADMIN ROLE - Full Access (except role/permission management)
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all()->except([
            'manage roles',
            'manage permissions',
            'assign roles',
            'manage system',
        ]));

        // TEACHER ROLE - Limited Access
        $teacher = Role::create(['name' => 'teacher']);
        $teacher->givePermissionTo([
            'view users',
            'view students',
            'view classes',
            'view schedules',
            'mark attendance',
            'view own class attendance',
            'edit attendance',
            'create homework',
            'view homework',
            'edit homework',
            'grade homework',
            'view own homework',
            'create progress',
            'view progress',
            'edit progress',
            'view own class progress',
            'upload resources',
            'view resources',
            'generate reports',
            'view reports',
        ]);

        // PARENT ROLE - View Only (for their children)
        $parent = Role::create(['name' => 'parent']);
        $parent->givePermissionTo([
            'view child attendance',
            'submit homework',
            'view child homework',
            'view child progress',
            'view resources',
        ]);

        $this->command->info('✓ Roles and permissions created successfully!');
        $this->command->info('✓ Superadmin role: Ultimate access + role management');
        $this->command->info('✓ Admin role: Full access (except role management)');
        $this->command->info('✓ Teacher role: Class management & teaching tools');
        $this->command->info('✓ Parent role: View children\'s information');
    }
}