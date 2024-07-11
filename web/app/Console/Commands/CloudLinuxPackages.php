<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CloudLinuxPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:cloud-linux-packages {--owner}  {--name}';

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
  "data": [
    {
      "name": "package",
      "owner": "root"
    },
    {
      "name": "package",
      "owner": "admin"
    },
    {
      "name": "package",
      "owner": "reseller"
    }
  ],
  "metadata": {
    "result": "ok"
  }
}';



    }
}
