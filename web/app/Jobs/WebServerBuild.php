<?php

namespace App\Jobs;

use App\MasterDomain;
use App\Models\Domain;
use App\Server\Helpers\OS;
use App\Server\Helpers\PHP;
use App\Services\Domain\DomainService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class WebServerBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fixPermissions = false;

    /**
     * Create a new job instance.
     */
    public function __construct($fixPermissions = false)
    {
        $this->fixPermissions = $fixPermissions;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $getDomains = Domain::whereNot('status','<=>', 'broken')->get();

        Bus::chain([
            new DomainPHPFPMBuild($getDomains),
            new ApacheBuild($getDomains, $this->fixPermissions),
        ])->dispatch();

    }
}
