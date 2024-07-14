<?php

namespace Database\Seeders\HostingSubscription;

use App\Models\Customer;
use App\Models\HostingSubscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\HostingPlan;

class HostingSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $customers = Customer::all();
        $hostingPlans = HostingPlan::all();

        foreach($customers as $customer){

            $hostingPlan = $hostingPlans->random();

            HostingSubscription::create([
//                'external_id' => Str::uuid(),
                'domain' => Str::random(10) . '.com',
                'customer_id' => $customer->id,
                'hosting_plan_id' => $hostingPlan->id,
                'system_username' => $customer->name,
                'system_password' => $customer->password,
                'description' => 'Description ' . $customer->id,
                'setup_date' => Carbon::now()->subDays(rand(0, 365)),
                'expiry_date' => Carbon::now()->addDays(rand(0, 365)),
                'renewal_date' => Carbon::now()->addDays(rand(30, 90))
            ]);
        }

    }
}
