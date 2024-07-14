<?php

namespace Database\Seeders\HostingPlan;

use App\Models\HostingPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HostingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for($i = 0; $i < 5; $i++) {

            HostingPlan::create([
                'name' => 'Package ' . $i,
//                'slug' => 'package-' . $i,
                'description' => 'Description for Package ' . $i,
                'disk_space' => rand(10, 100),
                'bandwidth' => rand(100, 1000),
                'databases' => rand(1, 10),
                'ftp_accounts' => rand(1, 10),
                'email_accounts' => rand(1, 10),
                'subdomains' => rand(1, 10),
                'parked_domains' => rand(1, 5),
                'addon_domains' => rand(1, 5),
                'ssl_certificates' => rand(1, 5),
                'daily_backups' => rand(1, 5),
                'free_domain' => rand(0, 1) == 1,
                'additional_services' => json_encode(['service' . rand(1, 5) => 'value' . rand(1, 5)]),
                'features' => json_encode(['feature' . rand(1, 5) => 'value' . rand(1, 5)]),
                'limitations' => json_encode(['limitation' . rand(1, 5) => 'value' . rand(1, 5)]),
                'default_server_application_type' => 'nginx',
                'default_server_application_settings' => json_encode(['setting' . rand(1, 5) => 'value' . rand(1, 5)])

            ]);
        }
    }
}
