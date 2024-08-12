<?php

namespace App\Jobs;

use App\Models\DirectoryPrivacy;
use App\Models\Domain;
use App\Models\HostingSubscription;
use App\Server\Helpers\PHP;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApacheHtConfigBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fixPermissions = false;
    public $hostingSubscriptionId;

    public function __construct($fixPermissions = false, $hostingSubscriptionId)
    {
        $this->fixPermissions = $fixPermissions;
        $this->hostingSubscriptionId = $hostingSubscriptionId;
    }

    public function handle($model = null)
    {

        $password = $model->password ?? '';
        $records = DirectoryPrivacy::where('hosting_subscription_id', $this->hostingSubscriptionId)
//            ->whereNotIn('password', [$password])
            ->get()
            ->groupBy('directory');

        $directories = $records->isEmpty() ? [$model->directory] : $records->keys()->toArray();

        if (!in_array('/', $directories)) {
            $directories[] = '/';
        }

        if($model !== null) {
            if(!in_array($model->directory, $directories)) {
                $directories[] = $model->directory;
            }

            if(!in_array($model->getOriginal('directory'), $directories)) {
                $directories[] = $model->getOriginal('directory');
            }
        }

        $domain = Domain::where('hosting_subscription_id', $this->hostingSubscriptionId)->first();

        $hostingSubscription = HostingSubscription::where('id', $this->hostingSubscriptionId)->first();

        $phpVersion = $domain->server_application_settings['php_version'] ?? null;
        $phpVersion = $phpVersion ? PHP::getPHPVersion($phpVersion) : [];

        foreach ($directories as $directory) {
            $htPasswdRecords = $records->get($directory, collect())->map(fn($record) => "{$record->username}:{$record->password}")->toArray();
            $htAccessFilePath = str_replace('//', '/', "{$directory}/.htaccess");
            $htPasswdFilePath = str_replace('//', '/', "{$directory}/.htpasswd");

            $label = $records->isEmpty() || $records->get($directory) === null
                ? null
                : $records->get($directory)->first()->label;

            $hotlinkData = $this->getHotlinkData($directory, $hostingSubscription->hotlinkProtection);
            $htViews = $this->getHtFileConfig($label, $phpVersion, $htPasswdFilePath, $htPasswdRecords, $hotlinkData);
            $htAccessFileRealPath = '/home/' . $hostingSubscription->system_username . $htAccessFilePath;
            $htPasswdFileRealPath = '/home/' . $hostingSubscription->system_username . $htPasswdFilePath;
            $this->updateSystemFile($htAccessFileRealPath, $htViews['htaccessContent']);
            $this->updateSystemFile($htPasswdFileRealPath, $htViews['htpasswdContent']);
        }
    }

    public function getHotlinkData($directory, $hotlinkProtectionData)
    {
        $currentDirectory = !$directory ? '/' : $directory;

        if ($hotlinkProtectionData && $currentDirectory === '/') {

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

    public function getHtFileConfig($label, $phpVersion, $htPasswdFilePath, $htPasswdRecords, $hotlinkData)
    {
        $htaccessContent = view('server.samples.apache.php.htaccess', [
            'phpVersion' => $phpVersion,
            'dPrivacyContent' => [
                'auth_name' => $label,
                'auth_user_file' => $htPasswdFilePath,
                'hotlinkData' => $hotlinkData
            ],
        ])->render();

        $htaccessContent = preg_replace_callback(
            '/(^\s*)(Rewrite.*|$)/m',
            function ($matches) {
                return str_repeat(' ', 4) . trim($matches[0]);
            },
            $htaccessContent
        );

        $htpasswdContent = view('server.samples.apache.php.htpasswd', [
            'dPrivacyContent' => $htPasswdRecords
        ])->render();

        $htpasswdContent = preg_replace_callback(
            '/(^\s*)(Rewrite.*|$)/m',
            function ($matches) {
                return str_repeat(' ', 4) . trim($matches[0]);
            },
            $htpasswdContent
        );

        return [
            'htaccessContent' => $htaccessContent,
            'htpasswdContent' => $htpasswdContent,
        ];
    }

    private function updateSystemFile($filePath, $newContent)
    {
        $existingContent = file_exists($filePath) ? file_get_contents($filePath) : '';
        $updatedContent = $this->replaceContentBetweenComments($existingContent, $newContent);
        file_put_contents($filePath, $updatedContent);
    }

    private function replaceContentBetweenComments($existingContent, $newContent)
    {
        $startComment = "# BEGIN PanelOmega-generated handler, do not edit";
        $endComment = "# END PanelOmega-generated handler, do not edit";

        $pattern = '/(' . preg_quote($startComment, '/') . ')(.*?)(?=' . preg_quote($endComment, '/') . ')/s';
        $contentToAdd = '';

        if (preg_match($pattern, $newContent, $matches)) {
            $contentToAdd = trim($matches[2]);
        }

        if (preg_match($pattern, $existingContent)) {
            $existingContent = preg_replace($pattern, "$startComment\n$contentToAdd\n", $existingContent);
        } else {
            $existingContent .= "\n$startComment\n$contentToAdd\n$endComment\n";
        }

        return preg_replace('/(\n\s*\n)+/', "\n", $existingContent);
    }
}
