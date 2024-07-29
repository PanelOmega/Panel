<?php

namespace App\Services\FtpConnections;

use App\Models\Customer;

class FtpConnectionsService
{

    public static function getCurrentFtpConnections()
    {
        $logFile = '/var/log/vsftpd.log';

        $userName = substr(self::getConnectionName(), 0, 7);

        $command = "ps aux | grep 'ftp' | grep '{$userName}' | awk '{print $2}' | head -n -1";
        $output = shell_exec($command);

        $pids = explode("\n", trim($output));
        $pids = array_filter($pids);
        $ftpConnections = [];

        foreach ($pids as $pid) {

            $command = "ps -eo pid,stat | grep '$pid ' | awk '{print $2}'; ";

            $command .= "grep '$pid' $logFile | grep '230 Login successful' | awk -F'[][]' '{print \$1, \$4, \$5}'";

            $output = shell_exec($command);
            $lines = explode("\n", trim($output));

            if (count($lines) >= 2) {

                $status = trim($lines[0]);
                $isActive = (trim($status) !== 'R' && trim($status) !== 'R+') ? 'IDLE' : 'ACTIVE';


                $ftpData = $lines[1];
                preg_match('/^(\w+ \w+ \d+ \d{2}:\d{2}:\d{2} \d{4})\s+(\S+)\s+FTP response: Client "([^"]+)",/', $ftpData, $matches);

                $ftpConnections[] = [
                    'user' => $matches[2],
                    'login_time' => trim(str_replace('"', '', $matches[1]), ", \t\n\r\0\x0B"),
                    'logged_in_from' => $matches[3],
                    'status' => $isActive,
                    'process_id' => $pid
                ];
            }
        }
        return $ftpConnections;
    }

    public static function disconnectFtpConnection($pid)
    {
        $command = "kill -9 $pid";
        $output = shell_exec($command);

        if (str_contains($output, "No such process")) {
            return false;
        }
        return true;
    }

    public static function getConnectionName()
    {
        $hostingSubscription = Customer::getHostingSubscriptionSession();
        return $hostingSubscription->system_username;
    }

}
