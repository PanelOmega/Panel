<?php

namespace App\Console\Commands;

use App\Jobs\ApacheBuildUpdateLog;
use App\Jobs\DomainPHPFPMBuild;
use App\Models\Domain;
use Illuminate\Console\Command;

class UpdateMyApacheLogConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:update-apache-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates My-Apache logging pattern';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domains = Domain::whereNot('status','<=>', 'broken')->get();

        $this->info('Configuring logs.');
        $build = new DomainPHPFPMBuild($domains);
        $build->handle();
        $updateLog = new ApacheBuildUpdateLog(false, $domains);
        $updateLog->handle();
        $this->info('Logs configured.');
    }

}
