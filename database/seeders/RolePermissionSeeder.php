<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define the 5 System Roles (SRS Section 3)
        $rolesData = [
            'super_admin' => [
                'name' => 'Super Admin',
                'description' => 'Paramount access to the entire platform, roles, permissions, and settings.',
            ],
            'admin' => [
                'name' => 'Admin',
                'description' => 'Back-office access, view users, fund users from own wallet, reset passwords, process manual jobs.',
            ],
            'staff' => [
                'name' => 'Staff',
                'description' => 'Restricted backend access, pick and process manual service requests.',
            ],
            'agent' => [
                'name' => 'Agent',
                'description' => 'Normal platform user with extra ability to fund other users from own wallet.',
            ],
            'end_user' => [
                'name' => 'End User',
                'description' => 'Standard customer with access to services and personal wallet.',
            ],
        ];

        $roles = [];
        foreach ($rolesData as $slug => $data) {
            $roles[$slug] = Role::updateOrCreate(['slug' => $slug], $data);
        }

        // 2. Define Granular Permissions
        $permissionsData = [
            ['name' => 'Access Admin Backend', 'slug' => 'backend.access', 'group' => 'admin'],
            ['name' => 'Manage Platform Roles', 'slug' => 'roles.manage', 'group' => 'admin'],
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'users'],
            ['name' => 'Reset User Password', 'slug' => 'users.reset_password', 'group' => 'users'],
            ['name' => 'Fund User Wallets', 'slug' => 'wallets.fund_users', 'group' => 'wallet'],
            ['name' => 'Process Manual Requests', 'slug' => 'services.process_manual', 'group' => 'services'],
            ['name' => 'Refund Manual Requests', 'slug' => 'services.refund_manual', 'group' => 'services'],
            ['name' => 'Manage Announcements', 'slug' => 'announcements.manage', 'group' => 'admin'],
            ['name' => 'Manage Site Settings & Pricing', 'slug' => 'settings.manage', 'group' => 'admin'],
        ];

        $permissions = [];
        foreach ($permissionsData as $data) {
            $permissions[$data['slug']] = Permission::updateOrCreate(['slug' => $data['slug']], $data);
        }

        // 3. Assign Permissions to Roles
        // Super Admin gets all permissions
        $roles['super_admin']->permissions()->sync(collect($permissions)->pluck('id'));

        // Admin gets backend access, view users, reset password, fund users, process & refund manual jobs, manage announcements, manage site settings
        $roles['admin']->permissions()->sync([
            $permissions['backend.access']->id,
            $permissions['users.view']->id,
            $permissions['users.reset_password']->id,
            $permissions['wallets.fund_users']->id,
            $permissions['services.process_manual']->id,
            $permissions['services.refund_manual']->id,
            $permissions['announcements.manage']->id,
            $permissions['settings.manage']->id,
        ]);

        // Staff gets backend access, process manual jobs, fund users (where permitted)
        $roles['staff']->permissions()->sync([
            $permissions['backend.access']->id,
            $permissions['services.process_manual']->id,
            $permissions['wallets.fund_users']->id,
        ]);

        // Agent gets ability to fund users from own wallet, but NO backend.access!
        $roles['agent']->permissions()->sync([
            $permissions['wallets.fund_users']->id,
        ]);

        // 4. Create Demo Seed Accounts for Each Role
        $demoUsers = [
            'super_admin' => [
                'first_name' => 'Root',
                'surname' => 'SuperAdmin',
                'middle_name' => 'Global',
                'email' => 'superadmin@vsite.ng',
                'phone_number' => '08000000001',
                'business' => 'V-Portal HQ',
            ],
            'admin' => [
                'first_name' => 'Farouk',
                'surname' => 'Administrator',
                'middle_name' => 'Aliyu',
                'email' => 'admin@vsite.ng',
                'phone_number' => '08000000002',
                'business' => 'Federal Operations Hub',
            ],
            'staff' => [
                'first_name' => 'Zainab',
                'surname' => 'Officer',
                'middle_name' => 'Bello',
                'email' => 'staff@vsite.ng',
                'phone_number' => '08000000003',
                'business' => 'Verification Desk A',
            ],
            'agent' => [
                'first_name' => 'Emeka',
                'surname' => 'Nwosu',
                'middle_name' => 'Paul',
                'email' => 'agent@vsite.ng',
                'phone_number' => '08000000004',
                'business' => 'Emeka Digital Services',
            ],
            'end_user' => [
                'first_name' => 'Amina',
                'surname' => 'Danjuma',
                'middle_name' => null,
                'email' => 'user@vsite.ng',
                'phone_number' => '08000000005',
                'business' => 'Danjuma Logistics',
            ],
        ];

        foreach ($demoUsers as $roleSlug => $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make('Password123!'),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'is_active' => true,
                ])
            );

            $user->roles()->sync([$roles[$roleSlug]->id]);

            // Initialize Demo Wallets with SRS balances
            $initialBalances = [
                'super_admin' => 100000.00,
                'admin' => 50000.00, // SRS Section 5 Example: ₦50,000
                'staff' => 20000.00,
                'agent' => 30000.00,
                'end_user' => 8000.00, // SRS Section 14 Example: ₦8,000
            ];

            $walletService = app(\App\Services\WalletService::class);
            $wallet = $walletService->getOrCreateWallet($user);
            if ($wallet->balance == 0 && isset($initialBalances[$roleSlug])) {
                $walletService->deposit(
                    user: $user,
                    amount: $initialBalances[$roleSlug],
                    description: 'Initial Baseline Wallet Allocation'
                );
            }
        }
    }
}
