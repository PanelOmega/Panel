<?php

namespace App\Listeners;

use App\Events\HostingSubscriptionIsDeleting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CloudLinuxUserDeletingListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     */
    public function handle(HostingSubscriptionIsDeleting $event): void
    {
        shell_exec('/usr/share/cloudlinux/hooks/pre_modify_user.py delete --username ' . $event->model->system_username);
    }
}
