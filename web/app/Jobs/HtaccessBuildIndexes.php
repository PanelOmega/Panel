<?php

namespace App\Jobs;

use App\Jobs\Traits\HtaccessBuildTrait;
use App\Models\HostingSubscription\Index;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HtaccessBuildIndexes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtaccessBuildTrait;

    public $fixPermissions = false;

    public $hostingSubscription;
    public $startComment = '# Section managed by Panel Omega: Indexing, do not edit';
    public $endComment = '# End section managed by Panel Omega: Indexing Privacy';

    public $isDeleted = false;

    public function __construct($fixPermissions = false, $hostingSubscription)
    {
        $this->fixPermissions = $fixPermissions;
        $this->hostingSubscription = $hostingSubscription;
    }

    public static function getIndexType($hostingSubscriptionId, $directoryPath)
    {
        $indexType = Index::where('hosting_subscription_id', $hostingSubscriptionId)
            ->where('directory_real_path', $directoryPath)
            ->pluck('index_type')
            ->toArray();

        return $indexType;
    }

    public function handle($model)
    {
        $htAccessFilePath = "{$model->directory_real_path}/.htaccess";
        $indexContent = $this->isDeleted ? [] : $this->getIndexConfig($model->index_type);
        $htAccessView = $this->getHtAccessFileConfig($indexContent);
        $htAccessFileRealPath = "/home/{$this->hostingSubscription->system_username}/public_html/{$htAccessFilePath}";
        $this->updateSystemFile($htAccessFileRealPath, $htAccessView);
    }

    public function getIndexConfig($indexType)
    {

        $indexConfigArr = match ($indexType) {
            'No Indexing' => [
                'options' => 'Options -Indexes',
                'indexOptions' => ''
            ],
            'Filename Only' => [
                'options' => 'Options +Indexes',
                'indexOptions' => 'IndexOptions -HTMLTable -FancyIndexing'
            ],
            'Filename And Description' => [
                'options' => 'Options +Indexes',
                'indexOptions' => 'IndexOptions +HTMLTable +FancyIndexing'
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
}
