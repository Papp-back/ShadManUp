<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'avatar' => '',
            'referral' => 'CHNFOJ',
            'referrer' => null,
            'login' => '09123456789',
            'role' => 1, 
            'cellphone' => '09123456789',
            'national_code' => '1234567890',
            'email' => 'admin@shadmanu.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'firstname' => 'Admin',
            'lastname' => '',
            'phone_code' => '1234',
            'phone_code_send_time' => now(),
            'wallet' => '0'
        ]);
    }
}
