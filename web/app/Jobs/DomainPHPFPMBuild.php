<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DomainPHPFPMBuild implements ShouldQueue
{
    use Queueable;

    public $domains = [];

    /**
     * Create a new job instance.
     */
    public function __construct($domains)
    {
        $this->domains = $domains;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

    }
}
