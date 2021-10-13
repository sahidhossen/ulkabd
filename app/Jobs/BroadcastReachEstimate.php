<?php

namespace App\Jobs;

use App\Events\BroadcastReachEstimateNotification;
use App\FacebookAPI;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;

class BroadcastReachEstimate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $agent;

    public $broadcast;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $agent, $broadcast )
    {
        $this->agent = $agent;
        $this->broadcast = $broadcast;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $broadcastReachEstimateResponse = FacebookAPI::startReachEstimation($this->agent);

            if (isset($broadcastReachEstimateResponse['data']['reach_estimation_id'])) {
                usleep(20000000);

                $getReachEstimateResponse = FacebookAPI::getReachEstimation( $broadcastReachEstimateResponse['data']['reach_estimation_id'], $this->agent);

                if( isset( $getReachEstimateResponse['data']['reach_estimation'])){
                    $stat = [
                        'reach_estimation' => $getReachEstimateResponse['data']['reach_estimation'],
                        'reach_estimation_id' => $getReachEstimateResponse['data']['id']
                    ];
                    $this->broadcast->stat = json_encode($stat);
                    $this->broadcast->save();

                    $this->broadcast->stat = $stat;
                    $this->broadcast->creative = json_decode( $this->broadcast->creative, true );

                    event(
                        new BroadcastReachEstimateNotification(
                            $this->agent,
                            $this->broadcast
                        )
                    );
                }

            }
        } catch( Exception $e ) {
            throw $e;
        }

    }

    public function failed(Exception $exception) {
        Log::info('Job failed due to ' . $exception->getMessage());
    }
}
