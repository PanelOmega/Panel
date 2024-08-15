<?php

namespace App\Jobs;

use App\HtaccessBuildTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HtaccessBuildIndexes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtaccessBuildTrait;

    public $fixPermissions = false;
    public $model;
    public $hostingSubscription;
    public $startComment = '# Section managed by Panel Omega: Indexing, do not edit';
    public $endComment = '# End section managed by Panel Omega: Indexing Privacy';

    public $isDeleted = false;

    public function __construct($fixPermissions = false, $model = null, $hostingSubscription)
    {
        $this->fixPermissions = $fixPermissions;
        $this->model = $model;
        $this->hostingSubscription = $hostingSubscription;
    }

    public function handle()
    {
        $htAccessFilePath = ($this->model->directory === '/') ? "{$this->model->directory}.htaccess" : "{$this->model->directory}/.htaccess";
        $indexContent = $this->isDeleted ? [] : $this->getIndexConfig();
        $htAccessView = $this->getHtAccessFileConfig($indexContent);
        $htAccessFileRealPath = '/home/' . $this->hostingSubscription->system_username . $htAccessFilePath;
        $this->updateSystemFile($htAccessFileRealPath, $htAccessView);
    }

    public function getIndexConfig() {

        $indexConfigArr = match ($this->model->index_type) {
            'no_indexing' => [
                'Indexes' => '-',
            ],
            'show_filename_only' => [
                'Indexes' => '+',
                'HTMLTable' => '-',
                'FancyIndexing' => '-',
            ],
            'show_filename_and_description' => [
                'Indexes' => '+',
                'HTMLTable' => '+',
                'FancyIndexing' => '+',
            ],
            default => []
        };

        return $indexConfigArr;
    }

    public function getHtAccessFileConfig($indexContent)
    {
        $htaccessContent = view('server.samples.apache.php.indexes-htaccess', [
            'index' => $indexContent
        ])->render();

        $htaccessContent = preg_replace_callback(
            '/(^\s*)(Rewrite.*|$)/m',
            function ($matches) {
                return str_repeat(' ', 4) . trim($matches[0]);
            },
            $htaccessContent
        );
        return $htaccessContent;
    }

    public function isDeleted($isDeleted = false) {
        $this->isDeleted = $isDeleted;
    }
}
