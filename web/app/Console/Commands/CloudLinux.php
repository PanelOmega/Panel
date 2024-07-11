<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CloudLinux extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:cloud-linux {action}';

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

        $action = $this->argument('action');

        if ($action == 'ui-user-info') {

            echo '{
    "userName": "user1",
    "userId": 1000,
    "userType": "user",
    "baseUri": "/user2/lvemanager/",
    "assetsUri": "/userdata/assets/lvemanager",
    "lang": "en",
    "userDomain": "current-user-domain.com"
}';
        }
        if ($action == 'panel-info') {
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
}
