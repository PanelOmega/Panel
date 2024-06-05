<?php

namespace App\Server\Helpers;

class LinuxUser
{
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

        return [
            'success' => 'User created successfully',
            'output' => $output,
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

        $command = 'sudo adduser --disabled-password --gecos "" "' . $username . '"';
        $output .= shell_exec($command);

        $command = 'sudo usermod -a -G www-data ' . $username;
        $output .= shell_exec($command);

        $command = 'sudo echo ' . $username . ':' . $password . ' | chpasswd -e';
        $output .= shell_exec($command);

        $homeDir = '/home';
        if (substr(sprintf('%o', fileperms($homeDir)), -4) !== '0711') {
            $command = 'sudo chmod 711 /home';
            $output .= shell_exec($command);
        }

        $command = 'sudo chmod 711 /home/' . $username;
        $output .= shell_exec($command);

        return [
            'success' => 'User created successfully',
            'output' => $output,
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
}
