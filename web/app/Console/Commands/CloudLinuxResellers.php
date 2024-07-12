<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class CloudLinuxResellers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'omega:cloud-linux-resellers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected function configure(): void
    {
        $this
            ->addOption('name', 'n', InputOption::VALUE_OPTIONAL, 'Reseller name')
        ;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

//        $action = $this->argument('action');

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
}
';



    }
}
