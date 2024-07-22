<?php

namespace App\Livewire;

use App\Services\FtpConnections\FtpConnectionsService;
use Livewire\Component;

class DisconnectFtpConnection extends Component
{

    public $pid;

//    public $statusMessage;

    public function disconnect()
    {
        if (FtpConnectionsService::disconnectFtpConnection($this->pid)) {
            $this->dispatch('ftp-connection-disconnected', [
                'type' => 'success',
                'message' => 'Disconnected successfully!']);
        } else {
            $this->dispatch('ftp-connection-disconnected', [
                'type' => 'error',
                'message' => 'No such process!'
            ]);
        }

    }

    public function render()
    {
        return view('livewire.disconnect-ftp-connection');
    }
}
