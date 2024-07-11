<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CloudLinuxDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'omega:cloud-linux-domains {--owner=} {--name=} {--with-php=}';

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

            echo '{
  "data": {
    "domain.com": {
      "owner": "username",
      "document_root": "/home/username/public_html/",
      "is_main": true
    },
    "subdomain.domain.com": {
      "owner": "username",
      "document_root": "/home/username/public_html/subdomain/",
      "is_main": false
    }
  },
  "metadata": {
    "result": "ok"
  }
}
';



    }
}
