<?php

namespace App\Jobs;

use App\Jobs\Traits\HtaccessBuildTrait;
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

    public $hostingSubscription;
    public $startComment = '# Section managed by Panel Omega: Hotlink Protection, do not edit';
    public $endComment = '# End section managed by Panel Omega: Hotlink Protection';

    public function __construct($fixPermissions = false, $hostingSubscription)
    {
        $this->fixPermissions = $fixPermissions;
        $this->hostingSubscription = $hostingSubscription;
    }

    public function handle()
    {
        $subscription = HostingSubscription::where('id', $this->hostingSubscription->id)->first();
        $htAccessFilePath = '/public_html/.htaccess';
        $hotlinkData = $this->getHotlinkData($subscription->hotlinkProtection);
        $htAccessView = $this->getHtAccessFileConfig($hotlinkData);
        $htAccessFileRealPath = '/home/' . $subscription->system_username . $htAccessFilePath;
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
                    'subdomain' => isset($parsedUrl['host']) ? explode('.', $parsedUrl['host'])[0] : 'www',
                    'domain' => isset($parsedUrl['host']) ? implode('.', array_slice(explode('.', $parsedUrl['host']), -2)) : '',
                    'full_url' => $url,
                ];
            }, $urlAllowAccessArray);

            $rewriteEngine = 'RewriteEngine on';
            $rewriteCond = 'RewriteCond %{HTTP_REFERER} !^$';

            $allowedUrls = [];
            foreach ($urls as $url) {
                $allowedUrls[] = "RewriteCond %{HTTP_REFERER} !^{$url['protocol']}://{$url['subdomain']}.{$url['domain']}/?$ [NC]";
                $allowedUrls[] = "RewriteCond %{HTTP_REFERER} !^{$url['protocol']}://{$url['subdomain']}.{$url['domain']}/?.* [NC]";

                if (empty($url['subdomain']) || $url['subdomain'] === 'www') {
                    $allowedUrls[] = "RewriteCond %{HTTP_REFERER} !^{$url['protocol']}://www.{$url['domain']}/?$ [NC]";
                    $allowedUrls[] = "RewriteCond %{HTTP_REFERER} !^{$url['protocol']}://www.{$url['domain']}/?.* [NC]";
                }
            }

            $blockedExtensions = rtrim(preg_replace('/\s+/', '', $hotlinkProtectionData->block_extensions), ',');
            $blockedExtensionsFormatted = str_replace(',', '|', trim($blockedExtensions, ','));
            $redirectTo = $hotlinkProtectionData->redirect_to;
            $rewriteRule = "RewriteRule .*\\.({$blockedExtensionsFormatted})$ {$redirectTo} [R,NC]";

            return [
                'enabled' => $hotlinkProtectionData->enabled,
                'rewriteEngine' => $rewriteEngine ?? '',
                'allowDirectRequests' => $hotlinkProtectionData->allow_direct_requests ? true : false,
                'rewriteCond' => $rewriteCond ?? '',
                'urlAllowAccess' => $allowedUrls,
                'rewriteRule' => $rewriteRule,
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
