<?php

namespace App\Console\Commands;

use App\Jobs\ApacheBuild;
use App\Jobs\WebServerBuild;
use App\Models\Domain;
use App\Services\Domain\DomainService;
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
        $domainService = new DomainService();
        $getActiveDomains = Domain::get();
        if ($getActiveDomains->count() > 0) {
            foreach ($getActiveDomains as $domain) {
                $this->info('Fixing domain permissions: ' . $domain->domain);
                try {
                    $domainService->configureHtaccess($domain->id);
                    $domainService->fixPermissions($domain->id, true, true);
                } catch (\Exception $e) {
                    $this->error('Failed to fix permissions for domain: ' . $domain->domain);
                    $this->error($e->getMessage());
                }
            }
        }

        $this->info('Rebuilding Apache configuration');

        $wsb = new WebServerBuild();
        $wsb->handle();
    }
}
