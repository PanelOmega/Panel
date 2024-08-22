<?php

namespace App\Jobs;

use App\Jobs\Traits\HtaccessBuildTrait;
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

    public static function getIndexType($directoryRealPath)
    {
        $indexType = 'Inherit';
        if (file_exists($directoryRealPath . '/.htaccess')) {
            $htaccessContent = file_get_contents($directoryRealPath . '/.htaccess');
            if (strpos($htaccessContent, '-Indexes') !== false) {
                $indexType = 'No Indexing';
            } elseif (strpos($htaccessContent, '+HTMLTable +FancyIndexing') !== false) {
                $indexType = 'Filename And Description';
            } elseif (strpos($htaccessContent, '-HTMLTable -FancyIndexing') !== false) {
                $indexType = 'Filename Only';
            }
        }
        return $indexType;
    }

    public function handle()
    {
        $htAccessFilePath = ($this->model->directory === '/') ? "{$this->model->directory_real_path}.htaccess" : "{$this->model->directory_real_path}/.htaccess";
        $indexContent = $this->isDeleted ? [] : $this->getIndexConfig();
        $htAccessView = $this->getHtAccessFileConfig($indexContent);
        $htAccessFileRealPath = "/home/{$this->hostingSubscription->system_username}/public_html/{$htAccessFilePath}";
        $this->updateSystemFile($htAccessFileRealPath, $htAccessView);
    }

    public function getIndexConfig()
    {

        $indexConfigArr = match ($this->model->index_type) {
            'No Indexing' => [
                'Indexes' => '-',
            ],
            'Filename Only' => [
                'Indexes' => '+',
                'HTMLTable' => '-',
                'FancyIndexing' => '-',
            ],
            'Filename And Description' => [
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

    public function isDeleted($isDeleted = false)
    {
        $this->isDeleted = $isDeleted;
    }
}
