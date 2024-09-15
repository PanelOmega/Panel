<?php

namespace App\Jobs;

use App\Jobs\Traits\HtaccessBuildTrait;
use App\Models\HostingSubscription\Redirect;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HtaccessBuildRedirects
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtaccessBuildTrait;

    public $fixedPermissions = false;
    public $redirectionsPath;
    public $hostingSubscriptionId;
    public $startComment = '# Section managed by Panel Omega: Redirects, do not edit';
    public $endComment = '# End section managed by Panel Omega: Redirects';

    public function __construct($fixedPermissions = false, $redirectionsPath, $hostingSubscriptionId)
    {
        $this->fixedPermissions = $fixedPermissions;
        $this->redirectionsPath = $redirectionsPath;
        $this->hostingSubscriptionId = $hostingSubscriptionId;
    }

    public function handle()
    {
        $redirectsData = $this->getRedirectsData();
        $htRedirectsView = $this->getHtAccessFileConfig($redirectsData);
        $this->updateSystemFile($this->redirectionsPath, $htRedirectsView);
    }

    public function getRedirectsData(): array
    {
        $records = Redirect::where('hosting_subscription_id', $this->hostingSubscriptionId)->get();
        $redirectRecords = [];
        foreach ($records as $record) {
            if ($record->domain === 'all_public_domains') {
                $rewriteCond = 'RewriteCond %{HTTP_HOST} ^.*$';
            } else {
                $escDomain = preg_quote($record->domain, '/');
                $rewriteCond = match ($record->match_www) {
                    'donotredirectwww' => 'RewriteCond %{HTTP_HOST} ^' . $escDomain . '$',
                    'only' => 'RewriteCond %{HTTP_HOST} ^www\.' . $escDomain . '$',
                    'redirectwithorwithoutwww' => 'RewriteCond %{HTTP_HOST} ^' . $escDomain . '$' . " [OR] \n" . 'RewriteCond %{HTTP_HOST} ^www\.' . $escDomain . '$',
                };
            }
            $escDirectory = $record->directory === '/' ? '^/$' : '^' . preg_quote($record->directory, '/') . '$';
            $redirectUrl = preg_quote($record->redirect_url, '/');

            $rewriteRule = "RewriteRule {$escDirectory} \"$redirectUrl\" [R={$record->status_code},L]";

            $redirectRecords[] = [
                'rewriteCond' => $rewriteCond,
                'rewriteRule' => $rewriteRule
            ];
        }

        return $redirectRecords;
    }

    public function getHtAccessFileConfig(array $redirectsData)
    {
        $htaccessContent = view('server.samples.apache.htaccess.redirects-htaccess', [
            'redirectsData' => $redirectsData,
        ])->render();

        return html_entity_decode($htaccessContent);
    }
}
