<?php

namespace Modules\Administration\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Modules\Administration\Mail\SubscriptionAdminEmail;
use Modules\Administration\Models\Admin;

class SendSubscriptionEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $admins = Admin::with('user')->get();
        foreach ($admins as $admin) {
            $user = $admin->user;

            if ($user) {
                Mail::to($user->email)->send(new SubscriptionAdminEmail([
                    'domain' => $event->domain,
                    'db_name' => $event->db_name,
                    'company' => $event->company,
                ]));
            }
        }
    }
}
