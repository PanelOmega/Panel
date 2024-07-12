<?php

namespace App\Console\Commands;

use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class CloudLinuxApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:cloud-linux-api {--request=} {--json-options=}';

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
        $jsonOptions = $this->option('json-options');
        $jsonOptions = json_decode($jsonOptions, true);
        if (!empty($jsonOptions)) {
            $jsonOptions = array_merge(['options'], $jsonOptions);
        }

        $request = $this->option('request');

        if ($request == 'panel-info') {
            return $this->panelInfo();
        }

        if ($request == 'ui-user-info') {
            return $this->uiUserInfo();
        }

        if ($request == 'users') {
            return $this->users($jsonOptions);
        }

        if ($request == 'admins') {
            return $this->admins($jsonOptions);
        }

        if ($request == 'packages') {
            return $this->packages($jsonOptions);
        }

    }

    public function users($jsonOptions)
    {
        $input = new ArgvInput($jsonOptions, new InputDefinition(array(
            new InputOption('owner', 'o', InputOption::VALUE_OPTIONAL),
            new InputOption('package-name', 'p', InputOption::VALUE_OPTIONAL),
            new InputOption('package-owner', 'w', InputOption::VALUE_OPTIONAL),
            new InputOption('username', 'u', InputOption::VALUE_OPTIONAL),
            new InputOption('unix-id', 'i', InputOption::VALUE_OPTIONAL),
            new InputOption('fields', 'f', InputOption::VALUE_OPTIONAL)
        )));
        $options = $input->getOptions();

        $hostingSubscriptions = [];
        $getHostingSubscriptions = HostingSubscription::all();
        if ($getHostingSubscriptions) {
            foreach ($getHostingSubscriptions as $getHostingSubscription) {
                $hostingSubscriptions[] = [
                    'id' => $getHostingSubscription->id,
                    'username' => $getHostingSubscription->system_username,
                    'owner' => 'root',
                    'domain' => $getHostingSubscription->domain,
                    'package' => [
                        'name' => $getHostingSubscription->hostingPlan->name,
                        'owner' => 'root'
                    ],
                    'email' => $getHostingSubscription->customer->email,
                    'locale_code' => 'EN_us'
                ];
            }
        }

        echo json_encode([
            'data' => $hostingSubscriptions,
            'metadata' => [
                'result' => 'ok'
            ]
        ], JSON_PRETTY_PRINT);

    }

    public function packages($jsonOptions)
    {
        $input = new ArgvInput($jsonOptions, new InputDefinition(array(
            new InputOption('owner', 'o', InputOption::VALUE_OPTIONAL)
        )));
        $options = $input->getOptions();

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

    public function admins($jsonOptions)
    {
        $input = new ArgvInput($jsonOptions, new InputDefinition(array(
            new InputOption('name', 'n', InputOption::VALUE_OPTIONAL),
            new InputOption('is-main', 'm', InputOption::VALUE_OPTIONAL)
        )));
        $options = $input->getOptions();

        echo '
{
   "data":[
      {
         "name":"root",
         "unix_user":"root",
         "locale_code":"EN_us",
         "email":"admin1@domain.zone",
         "is_main":true
      }
   ],
   "metadata":{
      "result":"ok"
   }
}
';

    }

    public function uiUserInfo()
    {
        $uiUserInfo = [
            'userName' => 'user1',
            'userId' => 1000,
            'userType' => 'user',
            'baseUri' => '/user2/lvemanager/',
            'assetsUri' => '/userdata/assets/lvemanager',
            'lang' => 'en',
            'userDomain' => 'current-user-domain.com'
        ];
        echo json_encode($uiUserInfo, JSON_PRETTY_PRINT);
    }

    public function panelInfo()
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
