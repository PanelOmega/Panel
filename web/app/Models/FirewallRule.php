<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirewallRule extends Model
{
    use \Sushi\Sushi;

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
}
