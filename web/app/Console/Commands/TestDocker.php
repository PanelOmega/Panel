<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestDocker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'omega:test-docker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domainName = 'omega-'.time().'.test';
        $domainName = 'omega.test';

        $availablePortScript = base_path('app/Docker/Shell/GetAvailablePort.sh');
        shell_exec('chmod +x '.$availablePortScript);
        $getAvailablePort = shell_exec($availablePortScript);
        $getAvailablePort = (int) $getAvailablePort;

        $getAvailablePort = 80;

        $containerNameLower = Str::slug($domainName, '-');
        $containerNameLower = trim($containerNameLower);
        $containerNameLower = strtolower($containerNameLower);

        $dockerTemplate = view('docker.templates.web.base-nginx-php-yaml', [
            'externalPort' => $getAvailablePort,
            'containerName' => $domainName,
            'containerNameLower' => $containerNameLower,
        ])->render();

        $dockerTempPath = base_path('docker-temp');
        if (! is_dir($dockerTempPath)) {
            mkdir($dockerTempPath, 0777, true);
        }

        file_put_contents($dockerTempPath.'/docker-compose.yml', $dockerTemplate);

        shell_exec('cd '.$dockerTempPath.' && docker compose up -d');

        dd($dockerTemplate);

    }
}
