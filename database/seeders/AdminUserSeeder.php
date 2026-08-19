<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('INITIAL_ADMIN_EMAIL', 'admin@alwaleed.com');

        if (User::where('email', $email)->exists()) {
            $this->command?->info('Initial admin already exists; existing credentials were preserved.');
            return;
        }

        $password = (string) env('INITIAL_ADMIN_PASSWORD', '');

        if ($password === '') {
            $this->command?->warn('INITIAL_ADMIN_PASSWORD is not set; no admin user was created.');
            return;
        }

        User::create([
            'company_id' => null,
            'name' => (string) env('INITIAL_ADMIN_NAME', 'System Owner'),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'super_admin',
        ]);
    }
}
