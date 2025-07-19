<?php

namespace Database\Seeders;

use Domain\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $model = User::firstOrCreate(
            ['email' => 'administrator@example.com'],
            ['name' => 'Administrator', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );

        $model->assignRole('super-admin');
    }
}
