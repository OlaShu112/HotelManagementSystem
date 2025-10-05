<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Hotel;
use App\Models\Subscription;
use App\Models\HotelSubscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Define Permissions
        $permissions = [
            // Hotel Management
            'view hotels', 'create hotels', 'edit hotels', 'delete hotels',

            // User Management
            'view users', 'create users', 'edit users', 'delete users',

            // Subscription Management
            'view subscriptions', 'create subscriptions', 'edit subscriptions', 'delete subscriptions',

            // Room Management
            'view rooms', 'create rooms', 'edit rooms', 'delete rooms',

            // Reservation Management
            'view reservations', 'create reservations', 'edit reservations', 'delete reservations',

            // Hotel Subscription Management
            'view hotel subscriptions', 'edit hotel subscriptions', 'delete hotel subscriptions',

            // Financial Management
            'view invoices', 'create invoices', 'edit invoices', 'delete invoices',

            // System Management
            'manage settings', 'manage users', 'manage permissions', 'manage roles',

            // Reports Management
            'view reports', 'generate reports',

            // Security and Audit
            'view audit logs', 'edit audit logs',

            // Order Management
            'view orders', 'create orders', 'edit orders', 'delete orders',

            // Bar Orders Management
            'view bar orders', 'create bar orders', 'edit bar orders', 'delete bar orders',

            // Expense Management
            'view expenses', 'create expenses', 'edit expenses', 'delete expenses',

            // Expense Types Management
            'view expense types', 'create expense types', 'edit expense types', 'delete expense types',

            // Payment Types Management
            'view payment types', 'create payment types', 'edit payment types', 'delete payment types',
        ];

        // Create Permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Define Roles and Permissions
        $roles = [
            'Superadmin' => $permissions, // Superadmin gets all permissions
            'Hotel Admin' => [
                'view users', 'create users', 'edit users', 'delete users',
                'view rooms', 'create rooms', 'edit rooms', 'delete rooms',
                'view reservations', 'create reservations', 'edit reservations', 'delete reservations',
                'view hotel subscriptions', 'edit hotel subscriptions', 'delete hotel subscriptions',
                'view invoices', 'create invoices', 'edit invoices', 'delete invoices',
                'view reports', 'generate reports',
                'view orders', 'create orders', 'edit orders', 'delete orders',
                'view bar orders', 'create bar orders', 'edit bar orders', 'delete bar orders',
                'view expenses', 'create expenses', 'edit expenses', 'delete expenses',
                'view expense types', 'create expense types', 'edit expense types', 'delete expense types',
                'view payment types', 'create payment types', 'edit payment types', 'delete payment types',
            ],
            'Hotel Staff' => [
                'view rooms', 'view reservations', 'edit reservations',
                'view orders', 'create orders', 'edit orders', 'delete orders',
                'view bar orders', 'create bar orders', 'edit bar orders', 'delete bar orders',
                'view expenses', 'create expenses', 'edit expenses', 'delete expenses',
            ],
        ];

        // Create Roles and Sync Permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }

        // Create Subscription Plans
        $subscriptions = [
            ['name' => 'Basic Plan', 'price' => 50.00, 'duration' => 'Monthly', 'status' => 'active'],
            ['name' => 'Standard Plan', 'price' => 100.00, 'duration' => 'Monthly', 'status' => 'active'],
            ['name' => 'Premium Plan', 'price' => 200.00, 'duration' => 'Yearly', 'status' => 'active'],
        ];

        foreach ($subscriptions as $plan) {
            Subscription::firstOrCreate($plan);
        }

        // Create Superadmin User
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@hotelcrm.com'],
            [
                'username' => 'Superadmin',
                'password' => Hash::make('Password@1'),
                'role' => 'super-admin',
            ]
        );
        $superAdmin->assignRole('Superadmin');

        // Create Demo Hotels and Hotel Admins
        $hotels = [
            [
                'name' => 'KLOFT Hotels',
                'email' => 'klofthotels@hotelcrm.com',
                'admin_email' => 'admin.kloft@hotelcrm.com',
                'subscription' => 'Premium Plan',
            ],
        ];

        foreach ($hotels as $data) {
            // Create Hotel
            $hotel = Hotel::firstOrCreate([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            // Assign Subscription to Hotel
            $subscription = Subscription::where('name', $data['subscription'])->first();
            if ($subscription) {
                HotelSubscription::create([
                    'hotel_id' => $hotel->id,
                    'subscription_id' => $subscription->id,
                    'start_date' => now(),
                    'end_date' => now()->addMonth(),
                    'status' => 'active',
                ]);
            }

            // Create Hotel Admin
            $hotelAdmin = User::firstOrCreate(
                ['email' => $data['admin_email']],
                [
                    'username' => $data['name'] . ' Admin',
                    'password' => Hash::make('Password@1'),
                    'role' => 'system-admin',
                ]
            );
            $hotelAdmin->assignRole('Hotel Admin');
            $hotelAdmin->update(['hotel_id' => $hotel->id]); // Link admin to the hotel
        }

        // Clear Permission Cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
