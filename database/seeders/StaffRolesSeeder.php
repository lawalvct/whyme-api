<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StaffRolesSeeder extends Seeder
{
    /**
     * Separate permissions for content, money and settings, even if one person
     * holds several at first (ACC04, OPS01, docs/01 §5.1).
     */
    public function run(): void
    {
        $permissions = [
            'users.view', 'users.manage',
            'moderation.review', 'verification.review',
            'disputes.resolve', 'refunds.approve',
            'payouts.approve', 'payouts.approve_high',
            'pricing.manage', 'exports.financial',
            'settings.manage', 'admins.manage',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'admin');
        }

        $roles = [
            'super-admin' => [], // passes every check via Gate::before
            'moderator' => ['users.view', 'moderation.review', 'verification.review'],
            'finance' => ['users.view', 'disputes.resolve', 'refunds.approve', 'payouts.approve', 'exports.financial'],
            'support' => ['users.view'],
        ];

        foreach ($roles as $name => $rolePermissions) {
            Role::findOrCreate($name, 'admin')->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
