<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@fspmi.org'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->assignRole('super-admin');

        $operator = User::firstOrCreate(
            ['email' => 'operator@fspmi.org'],
            [
                'name' => 'Operator',
                'password' => Hash::make('password'),
            ]
        );
        $operator->assignRole('operator');
    }
}
