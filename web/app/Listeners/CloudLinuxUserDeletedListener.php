<?php

namespace App\Listeners;

use App\Events\HostingSubscriptionIsDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CloudLinuxUserDeletedListener
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
    public function handle(HostingSubscriptionIsDeleted $event): void
    {
        shell_exec('/usr/share/cloudlinux/hooks/post_modify_user.py delete --username ' . $event->model->system_username);
    }
}
