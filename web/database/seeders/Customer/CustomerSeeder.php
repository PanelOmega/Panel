<?php

namespace Database\Seeders\Customer;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        for($i = 0; $i < 5; $i++){

            Customer::create([
                'name' => 'Customer '.$i,
                'username' => 'test' . Str::random(5),
                'password' => Hash::make('123'),
                'email' => 'test' . $i . '@test.com',
                'phone' => rand(1000000000, 9999999999),
                'address' => 'Test Address' . Str::random(5),
                'city' => 'Test City' . Str::random(5),
                'state' => 'Test State' . Str::random(5),
                'zip' => rand(10000, 99999),
                'country' => 'Test Country' . Str::random(5),
                'company' => 'Test Company' . Str::random(5)
            ]);

        }

    }
}
