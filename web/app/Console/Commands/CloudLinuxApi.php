<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\HostingPlan;
use App\Models\HostingSubscription;
use App\Server\Helpers\LinuxUser;
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
    protected $signature = 'omega:cloud-linux-api {--request=} {--encoded-options=}';

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
        $encodedOptions = $this->option('encoded-options');
        $jsonOptions = base64_decode($encodedOptions);
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
        if ($request == 'resellers') {
            return $this->resellers($jsonOptions);
        }

        if ($request == 'domains') {
            return $this->domains($jsonOptions);
        }

//        if ($request == 'php') {
//            return $this->php($jsonOptions);
//        }

    }


    public function php($jsonOptions)
    {
         echo '{
  "data": [
    {
      "identifier":  "alt-php74",
      "version": "7.4",
      "modules_dir":  "/opt/alt/php74/usr/lib64/modules",
      "dir": "/opt/alt/php74/",
      "bin":  "/opt/alt/php74/usr/bin/php",
      "ini": "/opt/alt/php74/link/conf/default.ini"
    },
    {
      "identifier":  "ea-php74",
      "version": "7.4",
      "modules_dir":  "/opt/cpanel/ea-php74/usr/lib64/modules",
      "dir": "/opt/cpanel/ea-php74/",
      "bin":  "/opt/cpanel/ea-php74/usr/bin/php",
      "ini": "/opt/cpanel/ea-php74/etc/php.ini"
    }
  ],
  "metadata": {
    "result": "ok"
  }
}';
    }

    public function domains($jsonOptions)
    {
        $data = [];

        $findHostingSubscriptions = HostingSubscription::all();
        if ($findHostingSubscriptions) {
            foreach ($findHostingSubscriptions as $findHostingSubscription) {
                $findDomains = Domain::where('hosting_subscription_id', $findHostingSubscription->id)->get();
                if ($findDomains) {
                    foreach ($findDomains as $findDomain) {
                        $data[$findDomain->domain] = [
                            'owner' => $findHostingSubscription->system_username,
                            'document_root' => $findDomain->domain_public,
                            'is_main' => $findDomain->is_main
                        ];
                    }
                }
            }
        }

        echo json_encode([
            'data' => $data,
            'metadata' => [
                'result' => 'ok'
            ]
        ], JSON_PRETTY_PRINT);
    }

    public function resellers($jsonOptions)
    {
        echo '{
  "data": [
    {
      "name": "reseller",
      "locale_code": "EN_us",
      "email": "reseller@domain.zone",
      "id": 10001
    }
  ],
  "metadata": {
    "result": "ok"
  }
}';
    }

    public function users($jsonOptions)
    {
        $input = new ArgvInput($jsonOptions, new InputDefinition(array(
            new InputOption('owner', 'o', InputOption::VALUE_OPTIONAL),
            new InputOption('package-name', null, InputOption::VALUE_OPTIONAL),
            new InputOption('package-owner', null, InputOption::VALUE_OPTIONAL),
            new InputOption('username', null, InputOption::VALUE_OPTIONAL),
            new InputOption('unix-id', null, InputOption::VALUE_OPTIONAL),
            new InputOption('fields', null, InputOption::VALUE_OPTIONAL)
        )));
        $options = $input->getOptions();

        $hostingSubscriptions = [];
        $queryHostingSubscriptions = HostingSubscription::query();
        if (isset($options['username']) && !empty($options['username'])) {
            $queryHostingSubscriptions->where('system_username', $options['username']);
        }
        if (isset($options['unix-id']) && !empty($options['unix-id'])) {
            $queryHostingSubscriptions->where('system_user_id', $options['unix-id']);
        }

        $getHostingSubscriptions = $queryHostingSubscriptions->get();
        if ($getHostingSubscriptions) {
            foreach ($getHostingSubscriptions as $getHostingSubscription) {

                $hostingSubscription = [
                    'id' => $getHostingSubscription->system_user_id,
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

                $hostingSubscriptions[] = $hostingSubscription;
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
//        $input = new ArgvInput($jsonOptions, new InputDefinition(array(
//            new InputOption('owner', 'o', InputOption::VALUE_OPTIONAL)
//        )));
//        $options = $input->getOptions();

        $packages = [];

        $findHostingPlans = HostingPlan::all();
        if ($findHostingPlans) {
            foreach ($findHostingPlans as $findHostingPlan) {
                $packages[] = [
                    'name' => $findHostingPlan->name,
                    'owner' => 'root'
                ];
                continue;
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
//        $input = new ArgvInput($jsonOptions, new InputDefinition(array(
//            new InputOption('name', 'n', InputOption::VALUE_OPTIONAL),
//            new InputOption('is-main', 'm', InputOption::VALUE_OPTIONAL)
//        )));
//        $options = $input->getOptions();

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
                    'php_selector' => true,
                    'ruby_selector' => true,
                    'python_selector' => true,
                    'nodejs_selector' => true,
                    'mod_lsapi' => true,
                    'mysql_governor' => true,
                    'cagefs' => true,
                    'reseller_limits' => true,
                    'xray' => false,
                    'accelerate_wp' => false,
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
