<?php

namespace App\Jobs;

use App\Models\DirectoryPrivacy;
use App\Models\Domain;
use App\Models\HostingSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DirectoryPrivacyHtFilesBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fixPermissions = false;
    public $hostingSubscriptionId;

    public function __construct($fixPermissions = false, $hostingSubscriptionId) {
        $this->fixPermissions = $fixPermissions;
        $this->hostingSubscriptionId = $hostingSubscriptionId;
    }

    public function handle($model = null)
    {
        $password = $model->password ?? '';
        $records = DirectoryPrivacy::where('hosting_subscription_id', $this->hostingSubscriptionId)
            ->whereNotIn('password', [$password])
            ->get()
            ->groupBy('directory');

        $domain = Domain::where('hosting_subscription_id', $this->hostingSubscriptionId)->first();
        $systemUsername = HostingSubscription::where('id', $this->hostingSubscriptionId)->value('system_username');

        $phpVersion = $domain->server_application_settings['php_version'] ?? null;
        $phpVersion = $phpVersion ? PHP::getPHPVersion($phpVersion) : [];

        $directories = $records->isEmpty() ? [$model->directory] : $records->keys();

        foreach ($directories as $directory) {
            $htPasswdRecords = $records->get($directory, collect())->map(fn($record) => "{$record->username}:{$record->password}")->toArray();

            $htAccessFilePath = "{$directory}/.htaccess";
            $htPasswdFilePath = "{$directory}/.htpasswd";

            $label = $records->isEmpty() ? '' : $records->get($directory)->first()->label;
            $htViews = $this->getHtFileConfig($label, $phpVersion, $htPasswdFilePath, $htPasswdRecords);

            $htAccessFileRealPath = '/home/' . $systemUsername . $htAccessFilePath;
            $htPasswdFileRealPath = '/home/' . $systemUsername . $htPasswdFilePath;

            $this->updateSystemFile($htAccessFileRealPath, $htViews['htaccessContent']);
            $this->updateSystemFile($htPasswdFileRealPath, $htViews['htpasswdContent']);
        }
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

    public function getHtFileConfig($label, $phpVersion, $htPasswdFilePath, $htPasswdRecords)
    {
        $htaccessContent = view('server.samples.apache.php.htaccess', [
            'phpVersion' => $phpVersion,
            'dPrivacyContent' => [
                'auth_name' => $label,
                'auth_user_file' => $htPasswdFilePath,
            ],
        ])->render();

        $htpasswdContent = view('server.samples.apache.php.htpasswd', [
            'dPrivacyContent' => $htPasswdRecords
        ])->render();

        return [
            'htaccessContent' => $htaccessContent,
            'htpasswdContent' => $htpasswdContent,
        ];
    }
}
