<?php

namespace App\Jobs;

use App\FacebookBot;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;
use Illuminate\Support\Facades\Log;

class FacebookMessageProcessor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $facebookBot;
    protected $msg;
    protected $agent;

    public function __construct($msg, $agent)
    {
        $this->facebookBot = new FacebookBot();
        $this->msg = $msg;
        $this->agent = $agent;
    }

    public function handle()
    {
        $this->facebookBot->processMessage($this->msg, $this->agent);
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception) {
        Log::info('FacebookMessageProcessor Job failed due to ' . $exception->getMessage());
    }
}
