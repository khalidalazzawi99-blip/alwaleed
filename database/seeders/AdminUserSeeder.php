<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(

            ['email' => 'admin@alwaleed.com'],

            [
                'company_id' => null,

                'name' => 'Khalid Alazzawi',

                'email' => 'admin@alwaleed.com',

                'password' => Hash::make('12345678'),

                'role' => 'super_admin',
            ]

        );
    }
}