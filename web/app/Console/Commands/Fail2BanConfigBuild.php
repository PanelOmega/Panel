<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Fail2BanConfigBuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:fail2ban-config-build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets to default the config file for the the Fail2Ban service';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fail2banConfigBuild = new \App\Jobs\Fail2BanConfigBuild();
        $fail2banConfigBuild->handle();
    }
}
