<?php

namespace Modules\Administration\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Contracts\Tenant;

class TenantCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $tenant;


    /**
     * Create a new event instance.
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;

    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
