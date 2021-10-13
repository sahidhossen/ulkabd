<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\FacebookAPI;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class SetupMessengerInterface implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $agent;

    /**
     * SetupMessengerInterface constructor.
     * @param $agent
     */
    public function __construct($agent)
    {
        $this->agent = $agent;
    }

    /**
     * @return array
     */
    public function handle()
    {
        try {
            if ($this->agent->page_subscription == false) {
                $sRes = FacebookAPI::subscribeWebhookToPageEvents($this->agent->fb_access_token);

                Log::info('Fb subscription setup res: ');
                Log::info($sRes);

                if (isset($sRes['success']) && $sRes['success'] == true) {
                    $this->agent->page_subscription = true;
                    $this->agent->save();
                }
            }

            if ($this->agent->messenger_profile == false) {
                usleep(100000);
                $dwlRes = FacebookAPI::manageDomainsInWhitelisting('add', $this->agent->fb_access_token);

                Log::info('Fb domain whitelisting res: ');
                Log::info($dwlRes);

                usleep(100000);
                $pRes = FacebookAPI::setMessengerProfile($this->agent, $this->agent->fb_access_token);

                Log::info('Fb interface setup res: ');
                Log::info($pRes);

                if (isset($pRes['result']) && $pRes['result'] == 'success') {
                    $this->agent->messenger_profile = true;
                    $this->agent->save();
                }
            }
        }
        catch( Exception $e ) {
            Log::info('SetupMessengerInterface Exception: ' . $e->getMessage());
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception) {
        Log::info('SetupMessengerInterface Job failed due to ' . $exception->getMessage());
    }
}
