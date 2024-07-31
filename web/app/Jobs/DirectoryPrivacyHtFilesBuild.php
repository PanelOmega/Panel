<?php

namespace App\Jobs;

use App\Models\DirectoryPrivacy;
use App\Models\Domain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DirectoryPrivacyHtFilesBuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fixPermissions = false;

    public function __construct($fixPermissions = false) {
        $this->fixPermissions = $fixPermissions;
    }

    public function handle($hostingSubscriptionId, $model = null)
    {
        $password = $model->password ?? '';
        $records = DirectoryPrivacy::where('hosting_subscription_id', $hostingSubscriptionId)
            ->whereNotIn('password', [$password])
            ->get()
            ->groupBy('directory');
        
        $domain = Domain::where('hosting_subscription_id', $hostingSubscriptionId)->first();
        $phpVersion = $domain->server_application_settings['php_version'] ?? null;
        $phpVersion = $phpVersion ? PHP::getPHPVersion($phpVersion) : [];

        if ($records->isEmpty()) {
            $this->handleDeletingEvent($model, $phpVersion);
            return;
        }

        foreach ($records as $directory => $directoryRecord) {
            $htPasswdRecords = $directoryRecord->map(fn($record) => "{$record->username}:{$record->password}")->toArray();
            $this->updateHtFiles($directory, $directoryRecord->first()->label, $phpVersion, $htPasswdRecords);
        }
    }

    private function updateHtFiles($directory, $label, $phpVersion, $htPasswdRecords)
    {
        $startComment = "# BEGIN PanelOmega-generated handler, do not edit";
        $endComment = "# END PanelOmega-generated handler, do not edit";

        $htAccessFilePath = "$directory/.htaccess";
        $htPasswdFilePath = "$directory/.htpasswd";

        $htViews = $this->setHtFileConfig($label, $phpVersion, $htPasswdFilePath, $htPasswdRecords);

        $this->updateFile($htAccessFilePath, $htViews['htaccessContent'], $startComment, $endComment);
        $this->updateFile($htPasswdFilePath, $htViews['htpasswdContent'], $startComment, $endComment);
    }

    private function updateFile($filePath, $newContent, $startComment, $endComment)
    {
        $existingContent = file_exists($filePath) ? file_get_contents($filePath) : '';
        $updatedContent = $this->replaceContentBetweenComments($existingContent, $newContent, $startComment, $endComment);
        file_put_contents($filePath, $updatedContent);
    }

    private function replaceContentBetweenComments($existingContent, $newContent, $startComment, $endComment)
    {
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

    public function handleDeletingEvent($model, $phpVersion)
    {
        $this->updateHtFiles($model->directory, '', $phpVersion, []);
    }

    public function setHtFileConfig($label, $phpVersion, $htPasswdFilePath, $htPasswdRecords)
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
