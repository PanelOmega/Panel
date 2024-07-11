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
    protected $signature = 'omega:cloud-linux';

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

        $arguments = $this->getArguments();

        dd($arguments);


//        echo json_encode([
//            'data'=>[
//                'name' => 'PanelOmega',
//                'description' => 'Panel Omega is a cloud-based web hosting control panel that makes it easy to manage websites, databases, and email accounts.',
//                'version' => '1.0.0',
//                'user_login_url' => 'https://omega.com/login',
//            ],
//            'metadata'=>[]
//        ]);


        echo '{
	"data": {
		"name": "PanelOmega",
		"version": "1.0.1",
		"user_login_url": "http://demo.panelomega:8443/customer",
		"supported_cl_features": {
			"php_selector": true,
			"ruby_selector": true,
			"python_selector": true,
			"nodejs_selector": false,
			"mod_lsapi": true,
			"mysql_governor": true,
			"cagefs": true,
			"reseller_limits": true,
			"xray": false,
			"accelerate_wp": false,
      "autotracing": true
		}
	},
	"metadata": {
		"result": "ok"
	}
}';

    }
}
