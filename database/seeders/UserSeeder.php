<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'Mohannad',
            'last_name' => 'Fadous',
            'mobile_number' => '0796771485',
            'branch' => 'Jordan',
            'email' => 'mohanned.fadous@saveto.com',
            'password' => Hash::make('fadous98'), // Encrypt the password
        ]);
    }
}
