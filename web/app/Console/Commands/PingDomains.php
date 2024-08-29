<?php

namespace App\Console\Commands;

use App\Models\Domain;
use Illuminate\Console\Command;

class PingDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:ping-domains';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping all domains in the database and check if they are up or down.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $getDomains = Domain::get();

        foreach ($getDomains as $domainData) {

            $domain = $domainData->domain;

            $cmd = "curl -s -o /dev/null -w '%{http_code}' http://$domain";
            $response = shell_exec($cmd);
            if ($response == 200) {
                $this->info("Domain $domain is up and running");
            } else {
                $this->warn("Domain $domain is down");
            }

        }

        return 0;
    }
}
