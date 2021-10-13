<?php

namespace App\Jobs;

use App\FacebookAPI;
use App\StandardFacebookResponses;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class OrderStatusUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $external_id;
    public $title;
    public $subTitle;
    public $agent;

    public function __construct($external_id, $title, $subTitle, $agent)
    {
        $this->external_id = $external_id;
        $this->title = $title;
        $this->subTitle = $subTitle;
        $this->agent = $agent;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        FacebookAPI::sendFacebookMessage(
            $this->external_id,
            StandardFacebookResponses::orderStatusMessage(
                $this->title,
                $this->subTitle,
                "Continue Browsing"
            ),
            $this->agent,
            'MESSAGE_TAG',
            'NON_PROMOTIONAL_SUBSCRIPTION'
        );
    }
}
