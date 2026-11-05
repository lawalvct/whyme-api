<?php

namespace Database\Seeders;

use App\Modules\Identity\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Creates the first super-admin from ADMIN_EMAIL / ADMIN_PASSWORD in .env.
     * No default credentials are ever built in.
     */
    public function run(): void
    {
        $email = config('whyme.admin.seed_email');
        $password = config('whyme.admin.seed_password');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD not set: no super-admin created.');

            return;
        }

        $admin = Admin::firstOrCreate(['email' => $email], ['name' => 'Super Admin', 'password' => $password]);
        $admin->assignRole('super-admin');
    }
}
