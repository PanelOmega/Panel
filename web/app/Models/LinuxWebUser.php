<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinuxWebUser extends Model
{
    use \Sushi\Sushi;

    protected $fillable = [
        'username',
        'password',
    ];

    protected $schema = [
        'id' => 'integer',
        'username' => 'string',
        'password' => 'string',
        'home_dir' => 'string',
        'hosting_subscription' => 'string',
        'can_be_deleted' => 'boolean'
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $canBeDeleted = $model->_canBeDeleted($model->username, $model->id);
            if (!$canBeDeleted) {
                throw new \Exception('Cannot delete this user');
            }
            shell_exec('userdel -r ' . $model->username);
            shell_exec('rm -rf /home/' . $model->username);
        });

    }

    private function _canBeDeleted($username, $userId)
    {
        if ($username == 'root') {
            return false;
        }
        if ($userId == 0) {
            return false;
        }
        return true;
    }

    public function getRows()
    {
        $users = [];

        $homeDir = '/home';
        $homeDirScan = scandir($homeDir);
        if (!empty($homeDirScan)) {
            foreach ($homeDirScan as $dir) {

                if ($dir == '.' || $dir == '..') {
                    continue;
                }

                $systemUsername = $dir;
                $systemUserId = $this->_getLinuxUserIdByUsername($systemUsername);

                if (!$this->_canBeDeleted($systemUsername, $systemUserId)) {
                    continue;
                }

                $hostingSubscription = 'N/A';
                $findHostingSubscription = HostingSubscription::where('system_username', $systemUsername)
                    ->where('system_user_id', $systemUserId)
                    ->first();
                if (!empty($findHostingSubscription)) {
                    $hostingSubscription = $findHostingSubscription->domain;
                }

                $users[] = [
                    'id' => $systemUserId,
                    'username' => $systemUsername,
                    'password' => '********',
                    'home_dir' => $homeDir . '/' . $systemUsername,
                    'hosting_subscription' => $hostingSubscription,
                ];
            }
        }

        return $users;
    }

    private function _getLinuxUserIdByUsername($username)
    {
        $output = shell_exec('id -u ' . $username);
        $output = intval($output);

        return $output;
    }
}
