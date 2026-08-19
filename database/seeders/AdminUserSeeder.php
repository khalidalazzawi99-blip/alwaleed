<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) env('INITIAL_ADMIN_NAME', ''));
        $email = trim((string) env('INITIAL_ADMIN_EMAIL', ''));
        $password = (string) env('INITIAL_ADMIN_PASSWORD', '');

        if ($name === '' || $email === '' || $password === '') {
            $this->command?->warn('Initial admin environment variables are incomplete; no admin user was created.');
            return;
        }

        if (User::where('email', $email)->exists()) {
            $this->command?->info('Initial admin already exists; existing credentials were preserved.');
            return;
        }

        User::create([
            'company_id' => null,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'super_admin',
        ]);
    }
}
