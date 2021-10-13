<?php

namespace App\Jobs;

use App\FacebookAPI;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Exception;

class FacebookAPICall implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sender, $data, $agent, $resType;

    public function __construct($sender, $data, $agent, $resType)
    {
        $this->sender = $sender;
        $this->data = $data;
        $this->agent = $agent;
        $this->resType = $resType;
    }

    public function handle()
    {
        try {
            switch ($this->resType) {
                case 'data_responses': {
                    foreach($this->data as $response) {
                        if (isset($response['sender_action'])) {
                            $response = FacebookAPI::sendFacebookSenderAction($this->sender, $response['sender_action'],
                                $this->agent);

                            if ($response['error'] == true) {
                                Log::info('Error in data response: ' . $response['message']);
                                break;
                            }
                            else {
                                Log::info($response['data']);
                            }
                        }
                        else {
                            $response = FacebookAPI::sendFacebookMessage($this->sender, $response, $this->agent);

                            if ($response['error'] == true) {
                                Log::info('Error in data response: ' . $response['message']);
                                break;
                            }
                            else {
                                Log::info($response['data']);
                            }
                        }
                    }
                }
                    break;

                case 'text_responses': {
                    foreach($this->data as $textPart) {
                        $response = FacebookAPI::sendFacebookMessage($this->sender, ['text' => $textPart], $this->agent);

                        if ($response['error'] == true) {
                            Log::info('Error in data response: ' . $response['message']);
                            break;
                        }
                        else {
                            Log::info($response['data']);
                        }
                    }
                }
                    break;

                case 'rich_responses': {
                    foreach($this->data as $msg) {

                        $response = FacebookAPI::sendFacebookSenderAction($this->sender, 'typing_on',
                            $this->agent);

                        if ($response['error'] == true) {
                            Log::info('Error in data response: ' . $response['message']);
                            break;
                        }
                        else {
                            Log::info($response['data']);

                            usleep(100);

                            $response = FacebookAPI::sendFacebookMessage($this->sender, $msg, $this->agent);

                            if ($response['error'] == true) {
                                Log::info('Error in data response: ' . $response['message']);
                                break;
                            }
                            else {
                                Log::info($response['data']);
                            }
                        }
                    }
                }
                    break;

                default: break;
            }
        }
        catch( Exception $e ) {
            Log::info('FacebookAPICall Exception: ' . $e->getMessage());
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception) {
        Log::info('FacebookAPICall Job failed due to ' . $exception->getMessage());
    }
}
