<?php

namespace App\Jobs;

use App\HtaccessBuildTrait;
use App\Models\HostingSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HtaccessBuildHotlinkProtection implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HtaccessBuildTrait;

    public $fixPermissions = false;
    public $model;
    public $startComment = '# Section managed by panelOmega: Hotlink Protection, do not edit';
    public $endComment = '# End section managed by panelOmega: Hotlink Protection';

    public function __construct($fixPermissions = false, $model)
    {
        $this->fixPermissions = $fixPermissions;
        $this->model = $model;
    }

    public function handle()
    {

        $hostingSubscription = HostingSubscription::where('id', $this->model->hosting_subscription_id)->first();

        $htAccessFilePath = '/.htaccess';
        $hotlinkData = $this->getHotlinkData($hostingSubscription->hotlinkProtection);
        $htAccessView = $this->getHtAccessFileConfig($hotlinkData);
        $htAccessFileRealPath = '/home/' . $hostingSubscription->system_username . $htAccessFilePath;
        $this->updateSystemFile($htAccessFileRealPath, $htAccessView);
    }

    public function getHotlinkData($hotlinkProtectionData)
    {
        if ($hotlinkProtectionData) {

            $urlAllowAccessArray = explode(',', $hotlinkProtectionData->url_allow_access);
            $urls = array_map(function ($url) {
                $url = trim($url);
                $parsedUrl = parse_url($url);

                return [
                    'protocol' => $parsedUrl['scheme'] ?? 'http',
                    'subdomain' => isset($parsedUrl['host']) ? explode('.', $parsedUrl['host'])[0] : '',
                    'domain' => isset($parsedUrl['host']) ? implode('.', array_slice(explode('.', $parsedUrl['host']), -2)) : '',
                    'full_url' => $url,
                ];
            }, $urlAllowAccessArray);

            $blockedExtensions = rtrim(preg_replace('/\s+/', '', $hotlinkProtectionData->block_extensions), ',');
            $redirectTo = $hotlinkProtectionData->redirect_to;

            return [
                'enabled' => $hotlinkProtectionData->enabled,
                'allow_direct_requests' => $hotlinkProtectionData->allow_direct_requests ? true : false,
                'url_allow_access' => $urls,
                'block_extensions' => $blockedExtensions,
                'redirect_to' => $redirectTo
            ];
        }
        return [];
    }

    public function getHtAccessFileConfig($hotlinkData)
    {
        $htaccessContent = view('server.samples.apache.php.hotlink-protection-htaccess', [
            'hotlinkData' => $hotlinkData
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
