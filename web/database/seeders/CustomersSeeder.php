<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class CustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            'name' => 'Test Customer',
            'username' => 'test',
            'password' => Hash::make('123'),
            'email' => 'test@test.com',
            'phone' => '123456789',
            'address' => 'Test Address',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip' => 'Test Zip',
            'country' => 'Test Country',
            'company' => 'Test Company'
        ]);
    }
}
