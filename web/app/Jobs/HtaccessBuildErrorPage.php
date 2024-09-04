<?php

namespace App\Jobs;

use App\Jobs\Traits\ErrorCodeDefaultContentTrait;
use App\Jobs\Traits\HtaccessBuildTrait;
use App\Models\HostingSubscription;
use App\Models\HostingSubscription\ErrorPage;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HtaccessBuildErrorPage
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtaccessBuildTrait, ErrorCodeDefaultContentTrait;

    public $fixPermissions = false;
    public $startComment = '# Section managed by Panel Omega: Error Pages, do not edit';
    public $endComment = '# End section managed by Panel Omega: Error Pages';

    public $hostingSubscriptionId;
    public $errorPageData = [];

    public $errorPageContent;

    public function __construct($fixPermissions = false, $hostingSubscriptionId, $errorPageData = [])
    {
        $this->fixPermissions = $fixPermissions;
        $this->hostingSubscriptionId = $hostingSubscriptionId;
        $this->errorPageData = $errorPageData;
    }

    public function handle()
    {
        $this->addErrorPageToSystem($this->errorPageData);
        $errorPagePath = $this->errorPageData['path'];
        $errorDocuments = $this->getAllErrorDocuments($this->hostingSubscriptionId, $errorPagePath);
        $htaccessView = $this->getHtaccessErrorCodesConfig($errorDocuments);
        $htaccessSystemPath = $errorPagePath . '/.htaccess';
        $this->updateSystemFile($htaccessSystemPath, $htaccessView);
    }

    public function addErrorPageToSystem($errorPageData)
    {
        $errorCode = $errorPageData['error_code'];
        $errorPagePath = "{$errorPageData['path']}/{$errorCode}.shtml";
        $errorPageContent = $this->trimContent($errorPageData['content']);
        file_put_contents($errorPagePath, $errorPageContent);
    }

    public function trimContent($content)
    {
        $content = html_entity_decode($content);
        $content = str_replace("\u{A0}", ' ', $content);
        return trim($content);
    }

    public function getAllErrorDocuments($hostingSubscriptionId, $errorPagePath): array
    {
        $errorCodes = ErrorPage::where('hosting_subscription_id', $hostingSubscriptionId)
            ->pluck('error_code');

        $errorDocuments = [];
        foreach ($errorCodes as $error) {
            $errorDocuments[] = "ErrorDocument {$error} {$errorPagePath}/{$error}.shtml";
        }
        return $errorDocuments;
    }

    public function getHtaccessErrorCodesConfig($errorDocuments)
    {
        $htaccessErrorCodesContent = view('server.samples.apache.htaccess.error-page-htaccess', [
            'errorDocuments' => $errorDocuments,
        ])->render();

        return $htaccessErrorCodesContent;
    }

    public function getErrorPageContent($errorPage, $hostingSubscription)
    {
        $getErrorCode = function ($pageName) {
            if (preg_match('/^\d+/', $pageName, $matches)) {
                return $matches[0];
            }
            return null;
        };

        $errorCode = $getErrorCode($errorPage);

        $hostingSubscriptionErrorPage = ErrorPage::where('hosting_subscription_id', $hostingSubscription->id)
            ->where('name', $errorPage)
            ->first();

        $content = $hostingSubscriptionErrorPage ? $hostingSubscriptionErrorPage->content : null;
        return !empty($content) ? $content : $this->getErrorCodeDefaultContent($errorCode);
    }
}
