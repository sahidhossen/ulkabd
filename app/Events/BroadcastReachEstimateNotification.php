<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;

class BroadcastReachEstimateNotification implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $agent;
    public $broadcast;

    /**
     * BroadcastProductUploaded constructor.
     */
    public function __construct($agent, $broadcast) {
//        Log::info("event broadcast: ");
//        Log::info($broadcast);
        $this->agent = $agent;
        $this->broadcast = json_decode($broadcast, true);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-reach_estimate_'.$this->agent->user_id);
    }
}
