<?php

namespace App\Models\Traits;

use App\Models\Customer;
use App\Models\Database;
use App\Models\HostingPlan;

trait DiskUsageTrait
{
    public function getDirSize($directoryPath) {
        $command = "du -sh {$directoryPath}";
        $output = shell_exec($command);

        $mb = $this->toMB($output);
        $hostingPlanDiskSpace = $this->getHostingPlanDiskSpace();
        $percentageUsed = ($mb / $hostingPlanDiskSpace) * 100;

        return [
            'size' => $mb . 'MB',
            'disk_usage' => $percentageUsed
        ];
    }

    public function getHiddenDirSize(string $directoryPath) {
        $command = "du -ch {$directoryPath}.[!.]*/* | grep total$";
        $output = shell_exec($command);

        $mb = $this->toMB($output);
        $hostingPlanDiskSpace = $this->getHostingPlanDiskSpace();

        $percentageUsed = ($mb / $hostingPlanDiskSpace) * 100;

        return [
            'size' => $mb . 'MB',
            'disk_usage' => $percentageUsed
        ];
    }

    public function getDatabasesSize($hostingSubscriptionId) {

        $size = [];

        $findDbs = Database::where('hosting_subscription_id', $hostingSubscriptionId)->get();
        foreach ($findDbs as $db) {
            $size[] = $db->calculateDatabaseSize();
        }

        $dbSize = $this->toMB(array_sum($size));

        $hostingPlanDiskSpace = $this->getHostingPlanDiskSpace();
        $percentageUsed = ($dbSize / $hostingPlanDiskSpace) * 100;

        return [
            'size' => $dbSize . 'MB',
            'disk_usage' => $percentageUsed
        ];
    }

    public function getMailingListsSize() {
//        $path = '/usr/local/omega/3rdparty/mailman/lists';
//        $command = "du -sh {$path}";
//        $output = shell_exec($command);
//        $mb = $this->toMB($output);

        $mb = '0.00';

        $hostingPlanDiskSpace = $this->getHostingPlanDiskSpace();
        $percentageUsed = ($mb / $hostingPlanDiskSpace) * 100;

        return [
            'size' => $mb . 'MB',
            'disk_usage' => $percentageUsed
        ];
    }

    public function getEmailArchivesSize() {
//        $path = '/usr/local/omega/3rdparty/mailman/archives';
//        $command = "du -sh {$path}";
//        $output = shell_exec($command);
//        $mb = $this->toMB($output);
//        return $mb;
        $mb = '0.00';

        $hostingPlanDiskSpace = $this->getHostingPlanDiskSpace();
        $percentageUsed = ($mb / $hostingPlanDiskSpace) * 100;

        return [
            'size' => $mb . 'MB',
            'disk_usage' => $percentageUsed
        ];
    }

    public function getEmailAccountsSize(string $directoryPath) {
//        $path = $directoryPath . '/mail';
//        $command = "du -sh {$path}";
//        $output = shell_exec($command);
//        $mb = $this->toMB($output);
//        return $mb;
        $mb = '0.00';

        $hostingPlanDiskSpace = $this->getHostingPlanDiskSpace();
        $percentageUsed = ($mb / $hostingPlanDiskSpace) * 100;

        return [
            'size' => $mb . 'MB',
            'disk_usage' => $percentageUsed
        ];

    }

    public function getOtherUsageSize(string $directoryPath) {
//        $command = "du -sh /";
//        $outputSizeOutsideHome = shell_exec($command);
//
//        $mb = $this->toMB($outputSizeOutsideHome);
//        $mbOutsideHome = strtok($outputSizeOutsideHome, "\t");
//        $diskUsageOutsideHome = (float) substr($mbOutsideHome, 0, -1);

        $mb = 0.00;
        $hostingPlanDiskSpace = $this->getHostingPlanDiskSpace();
        $percentageUsed = ($mb / $hostingPlanDiskSpace) * 100;

        return [
            'size' => $mb . 'MB',
            'disk_usage' => $percentageUsed
        ];
    }

    public function toMB(string $output) {

        $output = strtok($output, "\t");
        preg_match('/([\d.]+)([KMGkmg]?)/', $output, $matches);
        $size = (float)$matches[1];
        $unit = strtoupper($matches[2]);

        switch($unit) {
            case 'K':
                $mb = $size / 1024;
                break;
            case 'M':
                $mb = $size;
                break;
            case 'G':
                $mb = $size * 1024;
                break;
            default:
                $mb = 0;
                break;
        }

        return number_format($mb, 2, '.', '');
    }

    public function getHostingPlanDiskSpace() {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        $hostingPlan = HostingPlan::where('id', $hostingSubscription->id)->get();
        return $hostingPlan[0]->disk_space;
    }
}
