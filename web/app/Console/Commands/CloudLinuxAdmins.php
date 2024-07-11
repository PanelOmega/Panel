<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CloudLinuxAdmins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'omega:cloud-linux-admins {--name=} {--is-main=}';

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
}
