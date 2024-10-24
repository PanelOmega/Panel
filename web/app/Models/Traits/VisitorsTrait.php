<?php

namespace App\Models\Traits;

use App\Models\Domain;

trait VisitorsTrait
{
    public function getDomains(string $hostingSubscriptionId)
    {
        $domains = Domain::where('hosting_subscription_id', $hostingSubscriptionId)
            ->pluck('domain');

        return $domains;
    }

    public function getSslStatus(): string
    {
        if ($_SERVER['HTTPS'] === 'on') {
            return '(SSL)';
        }

        return '(Non-SSL)';
    }

    public function getDomainLogData(): array
    {
        $path = '/etc/my-apache/logs/visitors_log';

        if (!file_exists($path)) {
            throw new \Exception('The log file does not exist');
        }
        $content = file_get_contents($path);
        $contentLines = explode(PHP_EOL, $content);

        $logs = [];
        $pattern = '/(?P<host>[^ ]+) (?P<ip>\d{1,3}(?:\.\d{1,3}){3}) - - \[(?P<time>.*?)\] "(?P<method>\w+) (?P<url>.*?) (?P<protocol>.*?)" (?P<status>\d{3}) (?P<size>\d+|-) "(?P<referring_url>.*?)" "(?P<user_agent>.*?)"/';
        foreach ($contentLines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                $domain = '';
                if (!filter_var($matches['host'], FILTER_VALIDATE_IP)) {
                    $domain = $matches['host'];
                }

                $time = date('m/d/y, g:i A', strtotime($matches['time']));

                $sizeInBytes = $matches['size'] !== '-' ? (int)$matches['size'] : 0;
                $referringUrl = $matches['referring_url'] !== '-' ? $matches['referring_url'] : '';
                $userAgent = $matches['user_agent'] !== '-' ? $matches['user_agent'] : '';

                $logs[] = [
                    'domain' => $domain,
                    'ip' => $matches['ip'],
                    'url' => $matches['url'],
                    'time' => $time,
                    'size' => $sizeInBytes,
                    'status' => $matches['status'],
                    'method' => $matches['method'],
                    'protocol' => $matches['protocol'],
                    'referring_url' => $referringUrl,
                    'user_agent' => $userAgent,
                ];
            }
        }
        return $logs;
    }

    public function getColumnOptions(): array
    {
        $columnOptions = [];
        $options = [
            'ip' => 'IP Address',
            'url' => 'URL',
            'time' => 'Time',
            'size' => 'Size (bytes)',
            'status' => 'Status',
            'method' => 'Method',
            'protocol' => 'Protocol',
            'referring_url' => 'Referring URL',
            'user_agent' => 'User Agent',
        ];

        foreach ($options as $key => $option) {
            $columnOptions[$key] = $option;
        }

        return $columnOptions;
    }

    public function getCurrentSizeSent(string $bytes) : string
    {
        $units = ['bytes', 'KB', 'MB', 'GB', 'TB'];
        $power = 0;

        while ($bytes >= 1024 && $power < count($units) - 1) {
            $bytes /= 1024;
            $power++;
        }
        return round($bytes, 2) . ' ' . $units[$power];
    }
}
