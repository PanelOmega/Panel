<?php

namespace App\Console\Commands;

use App\Models\HostingPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CloudLinuxPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:cloud-linux-packages {--o|owner=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

//        $action = $this->argument('action');

        $packages = [];

        $findHostingPlans = HostingPlan::all();
        if ($findHostingPlans) {
            foreach ($findHostingPlans as $findHostingPlan) {
                $packages[] = [
                    'name' => $findHostingPlan->name,
                    'owner' => 'root'
                ];
            }
        }

        echo json_encode([
            'data' => $packages,
            'metadata' => [
                'result' => 'ok'
            ]
        ], JSON_PRETTY_PRINT);

    }
}
