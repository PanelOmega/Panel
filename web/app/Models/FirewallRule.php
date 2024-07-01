<?php

namespace App\Models;

use App\Server\Helpers\OS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirewallRule extends Model
{
    use \Sushi\Sushi;

    protected $fillable = [
        'action',
        'port_or_ip',
        'comment',
    ];

    protected $schema = [
        'id'=>'integer',
        'action'=>'string',
        'direction'=>'string',
        'protocol'=>'string',
        'from_ip'=>'string',
        'from_port'=>'string',
        'to_ip'=>'string',
        'to_port'=>'string',
        'comment'=>'string',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            if ($model->to_port == '8443') {
                throw new \Exception('Cannot delete port 8443');
            }
            shell_exec('echo "y" | sudo ufw delete ' . $model->id);
        });

        static::creating(function ($model) {
            $model->_portAction($model->action, $model->port_or_ip, $model->comment);
            unset($model->port_or_ip);
        });
    }

    public function getRows()
    {
        // Get Linux Firewall Rules
        $firewallRules = shell_exec('sudo ufw status numbered | jc --ufw');
        $firewallRules = json_decode($firewallRules, true);

        if (!isset($firewallRules['status'])) {
            return [];
        }
        if ($firewallRules['status'] != 'active') {
            return [];
        }

        $rules = [];
        foreach ($firewallRules['rules'] as $firewallRule) {
            if (!isset($firewallRule['to_ports'])) {
                $firewallRule['to_ports'] = [' - '];
            }
            $rules[] = [
                'id' => $firewallRule['index'],
                'action' => $firewallRule['action'],
                'direction' => $firewallRule['action_direction'],
                'protocol' => $firewallRule['network_protocol'],
                'from_ip' => $firewallRule['from_ip'],
                'from_port' => $firewallRule['from_port_ranges'][0]['start'],
                'to_ip' => $firewallRule['to_ip'],
                'to_port' => $firewallRule['to_ports'][0],
                'comment' => $firewallRule['comment'],
            ];
        }

        return $rules;
    }

    public static function isEnabled()
    {
        $status = shell_exec('sudo ufw status');
        if (str_contains($status, 'Status: active')) {
            return true;
        }
        return false;
    }

    private static function _portAction($action, $portOrIp, $comment = '')
    {
        $command = 'sudo ufw ';
        $command .= $action . ' ';
        $command .= $portOrIp . ' ';
        $command .= 'comment "' . $comment . '"';

        shell_exec($command);
    }

    public static function enableSystemPorts()
    {
        self::_portAction('allow', '8443', 'PanelOmega - Admin');
        self::_portAction('allow', '80', 'PanelOmega - HTTP');
        self::_portAction('allow', '443', 'PanelOmega - HTTPS');
    }
    public static function enableFirewall()
    {
        $os = OS::getDistro();
        $output = shell_exec('sudo ufw --force enable');
        if (str_contains($output, 'Firewall is active')) {
            self::enableSystemPorts();
            return true;
        } else {
            if ($os == OS::UBUNTU) {
                shell_exec('sudo apt install ufw jc -y');
            } else if ($os == OS::ALMA_LINUX) {
                shell_exec('sudo dnf install ufw jc -y');
            }
            $output = shell_exec('sudo ufw --force enable');
            if (str_contains($output, 'Firewall is active')) {
                self::enableSystemPorts();
                return true;
            }
        }
        return false;
    }
}
