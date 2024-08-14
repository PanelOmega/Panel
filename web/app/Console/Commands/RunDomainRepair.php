<?php

namespace App\Console\Commands;

use App\Jobs\ApacheBuild;
use App\Models\Domain;
use Illuminate\Console\Command;

class RunDomainRepair extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:run-domain-repair';

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
        $getActiveDomains = Domain::get();
        if ($getActiveDomains->count() > 0) {
            foreach ($getActiveDomains as $domain) {
                $this->info('Fixing domain permissions: ' . $domain->domain);
                $domain->fixPermissions(true, true);
            }
        }

        $this->info('Rebuilding Apache configuration');

        $apacheBuild = new ApacheBuild();
        $apacheBuild->handle();
    }
}
