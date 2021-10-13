<?php

namespace App\Http\Controllers;

use App\Agents;
use App\FacebookBot;
use App\Jobs\FacebookMessageProcessor;
use App\Jobs\SetupMessengerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class FacebookMessengerController extends Controller
{
    use DispatchesJobs, Queueable;

//    protected $facebookBot;

    public function __construct()
    {
//        $this->facebookBot = new FacebookBot();
    }

    private function setupAgentInterface($agent) {
        if (
            $agent->fb_access_token != null
            &&
            $agent->is_fb_webhook == true
            &&
            (
                $agent->page_subscription == false
                ||
                $agent->messenger_profile == false
            )
        ) {
            $this->dispatch((new SetupMessengerInterface($agent))
                ->onQueue(config('queueNames.messenger_updater')));
        }
    }

    public function get_webhook(Request $request) {
        try {
            Log::info($request);
            Log::info($request->path());
            
            $agent_code = explode('/', $request->path())[0];
            $agent = Agents::where('agent_code', $agent_code )->first();

            if ($agent != null &&
                strcmp($agent->fb_verify_token, $request['hub_verify_token']) === 0) {

                Log::info('Success webhook!');

                $agent->is_fb_webhook = true;
                $agent->save();

                $this->setupAgentInterface($agent);

                return $request['hub_challenge'];
            }
            else {
                return [
                    'status' => 'error',
                    'error'  => new Exception('Could not verify.')
                ];
            }
        }
        catch( Exception $e ){
            return [
                'status' => 'error',
                'error'  => $e->getMessage()
            ];
        }
    }

    public function post_webhook(Request $request) {
        try {
            Log::info('POST FB webhook in path: ' . $request->path());
            Log::info($request);

            $agent_code = explode('/', $request->path())[0];

            // Getting agent from db for each message would be very costly
            // Set agent detail in redis
            $agent = Agents::getAgentFromCache($agent_code);

            // Do not accept POST request until apiai dev and client access tokens are set.
            if ($agent != null && $agent->apiai_client_access_token != null) {

                if (isset($request['entry'])) {

                    $entries = $request['entry'];

                    foreach($entries as $entry) {

                        if (isset($entry['messaging'])) {

                            $messagings = $entry['messaging'];

                            foreach($messagings as $msg) {

                                if (
                                    (isset($msg['message'])
                                        &&
                                        isset($msg['message']['is_echo']) === FALSE)
                                    ||
                                    (isset($msg['postback'])
                                        &&
                                        isset($msg['postback']['payload']))
                                ) {

                                    // Possibly dispatch this in a new job
//                                    $this->facebookBot->processMessage($msg, $agent);
                                    $this->dispatch((new FacebookMessageProcessor($msg, $agent))
                                        ->onQueue(config('queueNames.messenger_updater')));

                                }

                            }

                        }

                    }

                }

                return [
                    'status' => 'ok'
                ];
            }
            else {
                return [
                    'status' => 'error',
                    'error'  => new Exception('Could not verify.')
                ];
            }
        }
        catch( Exception $e ){
            return [
                'status' => 'error',
                'error'  => $e->getMessage()
            ];
        }
    }

}
