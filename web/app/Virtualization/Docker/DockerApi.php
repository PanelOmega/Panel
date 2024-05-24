<?php

namespace App\Virtualization\Docker;

class DockerApi
{
    public static function isDockerInstalled(): array
    {
        $dockerVersion = shell_exec('docker --version');
        if (str_contains($dockerVersion, 'Docker version')) {
            return [
                'status' => 'success',
                'message' => 'Docker is installed.',
                'version' => $dockerVersion
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Docker is not installed.'
        ];
    }
}
