<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CloudLinuxPanelInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:cloud-linux-panel-info';

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
        $panelInfo = [
            'data' => [
                'name' => 'PanelOmega',
                'version' => '1.0.0',
                'user_login_url' => '',
                'supported_cl_features' => [
                    'autotracing' => true
                ]
            ],
            'metadata' => [
                'result' => 'ok'
            ]
        ];
        echo json_encode($panelInfo, JSON_PRETTY_PRINT);

    }
}
