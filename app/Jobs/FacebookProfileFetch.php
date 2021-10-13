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

class FacebookProfileFetch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $agent;

    /**
     * FacebookProfileFetch constructor.
     * @param $user
     * @param $agent
     */
    public function __construct($user, $agent)
    {
        $this->user = $user;
        $this->agent = $agent;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->agent->fb_access_token != null && $this->user->agent_scoped_id != null) {
                $response = FacebookAPI::getUserProfile($this->user, $this->agent);

                Log::info('Fb User profile: ');
                Log::info($response);

                $this->user->first_name = isset($response['first_name']) ? $response['first_name'] : null;
                $this->user->last_name = isset($response['last_name']) ? $response['last_name'] : null;
                $this->user->profile_pic = isset($response['profile_pic']) ? $response['profile_pic'] : null;
                $this->user->local = isset($response['locale']) ? $response['locale'] : null;
                $this->user->gender = isset($response['gender']) ? $response['gender'] : null;

                $this->user->save();
            }
        }
        catch( Exception $e ) {
            Log::info('FacebookProfileFetch Exception: ' . $e->getMessage());
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception) {
        Log::info('FacebookProfileFetch Job failed due to ' . $exception->getMessage());
    }
}
