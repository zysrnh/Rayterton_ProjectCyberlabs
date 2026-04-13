<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\User::create([
            'email' => 'superadmin@rayterton.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => 'super_admin',
            'is_active' => true,
        ]);

        \App\Models\User::create([
            'email' => 'verifier@rayterton.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => 'verifier',
            'is_active' => true,
        ]);
    }
}