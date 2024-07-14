<?php

namespace App\Server\Helpers;

class LinuxUser
{

    public const USER_FILE_PERMISSION = 0644;
    public const USER_DIRECTORY_PERMISSION = 0711;
    public const USER_GROUP = 'www-data';

    public static function getLinuxUserIdByUsername($username)
    {
        $output = shell_exec('id -u ' . $username);
        $output = intval($output);

        return $output;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $email
     * @return string
     */
    public static function createUser(string $username, string $password, string $email)
    {
        $checkUser = self::getUser($username);
        if (!empty($checkUser)) {
            return [
                'error' => 'User already exists'
            ];
        }

        $output = '';

        $command = '/usr/sbin/useradd "' . $username . '" -c "' . $email . '" --no-create-home';
        $output .= shell_exec($command);

        $command = 'echo ' . $username . ':' . $password . ' | sudo chpasswd -e';
        $output .= shell_exec($command);

        $linuxUserId = LinuxUser::getLinuxUserIdByUsername($username);

        return [
            'success' => 'User created successfully',
            'output' => $output,
            'linuxUserId' => $linuxUserId,
        ];
    }

    /**
     * @param string $username
     * @param string $password
     * @return string
     */
    public static function createWebUser(string $username, string $password)
    {
        $output = '';

        $checkUser = self::getUser($username);
        if (!empty($checkUser)) {
            return [
                'error' => 'User already exists'
            ];
        }

        $distro = OS::getDistro();
        if ($distro === OS::UNKNOWN) {
            throw new \Exception('Unknown OS');
        }

        if ($distro === OS::DEBIAN || $distro === OS::UBUNTU) {
            $command = 'sudo adduser --disabled-password --gecos "" "' . $username . '"';
            $output .= shell_exec($command);
        } else if ($distro === OS::CLOUD_LINUX || $distro === OS::ALMA_LINUX || $distro === OS::CENTOS) {
            $command = 'sudo useradd "' . $username . '"';
            $output .= shell_exec($command);
        } else {
            throw new \Exception('Unsupported OS');
        }

        if ($distro === OS::DEBIAN || $distro === OS::UBUNTU) {
            $command = 'sudo usermod -a -G www-data ' . $username;
            $output .= shell_exec($command);
        }

        $command = 'sudo echo ' . $username . ':' . $password . ' | chpasswd -e';
        $output .= shell_exec($command);

        $homeDir = '/home';
        if (substr(sprintf('%o', fileperms($homeDir)), -4) !== '0711') {
            $command = 'sudo chmod 711 /home';
            $output .= shell_exec($command);
        }

        $command = 'sudo chmod 711 /home/' . $username;
        $output .= shell_exec($command);

        $linuxUserId = LinuxUser::getLinuxUserIdByUsername($username);

        return [
            'success' => 'User created successfully',
            'output' => $output,
            'linuxUserId' => $linuxUserId,
        ];
    }

    /**
     * @param string $username
     * @return string[]|null
     */
    public static function getUser(string $username)
    {
        $user = shell_exec('getent passwd ' . $username);
        if (empty($user)) {
            return null;
        }

        $user = explode(':', $user);

        return $user;
    }

    public static function deleteUser(string $username)
    {
        shell_exec('userdel -r '.$username);
        shell_exec('rm -rf /home/'.$username);

        return [
            'success' => 'User deleted successfully',
        ];
    }

}
