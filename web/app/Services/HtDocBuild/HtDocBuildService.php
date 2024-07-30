<?php

namespace App\Services\HtDocBuild;

use App\Models\Domain;
use App\Server\Helpers\PHP;

class HtDocBuildService
{
    public static function buildHtaccessByDomain(Domain $domain, array $dPrivacyContent = []): void
    {
        $phpVersion = [];

        if(isset($domain->server_application_settings['php_version'])) {
            $phpVersion = PHP::getPHPVersion($domain->server_application_settings['php_version']);
        }

        $htaccessContent = view('server.samples.apache.php.htaccess', [
            'phpVersion' => $phpVersion,
            'dPrivacyContent' => $dPrivacyContent,
        ])->render();

        file_put_contents($domain->domain_public . '/.htaccess', $htaccessContent);
    }

    public static function buildHtpasswdByDomain(Domain $domain, array $dPrivacyContent): void
    {

        $htPasswdPath = $domain->domain_public . '/.htpasswd';
        $existingContent = file_exists($htPasswdPath) ? file_get_contents($htPasswdPath) : '';
        $lines = explode("\n", $existingContent);
        $existingLine = '';

        foreach($lines as $line) {
            $trimmedLine = trim($line);
            if(strpos($trimmedLine, ':$2y$') !== false) {
                $existingLine .= $trimmedLine . "\n";
            }
        }

        $newLine = $dPrivacyContent['username'] . ':' . $dPrivacyContent['password'];
        $newContent = $existingLine .= $newLine . "\n";
        $htPasswdContent = view('server.samples.apache.php.htpasswd', [
            'dPrivacyContent' => explode("\n", $newContent),
        ])->render();

        file_put_contents($htPasswdPath, $htPasswdContent);
    }

    public static function deleteFromHtpasswdByDomain(Domain $domain, array $dPrivacyContent): void
    {
        $htPasswdPath = $domain->domain_public . '/.htpasswd';
        $existingContent = file_exists($htPasswdPath) ? file_get_contents($htPasswdPath) : '';
        $lines = explode("\n", $existingContent);

        $updatedContent = '';

        foreach($lines as $line) {
            $trimmedLine = trim($line);

            if(strpos($trimmedLine, ':$2y$') !== false) {
                if(explode(':', $trimmedLine)[0] !== $dPrivacyContent['username']) {
                    $updatedContent .= $trimmedLine . "\n";
                }
            }
        }
        $htPasswdContent = view('server.samples.apache.php.htpasswd', [
            'dPrivacyContent' => explode("\n", $updatedContent),
        ])->render();

        file_put_contents($htPasswdPath, $htPasswdContent);

    }
}
