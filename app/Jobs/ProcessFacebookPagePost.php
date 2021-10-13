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

class ProcessFacebookPagePost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $agent, $message, $products;
    private $mediaFbIds;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($agent, $message, Array $products)
    {
        $this->agent = $agent;
        $this->message = $message;
        $this->products = $products;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->mediaFbIds = [];

            // Upload unpublished photo page posts
            foreach($this->products as $product) {

                $url = null;
                if ($product->image_link) $url = $product->image_link;
                elseif ($product->is_image) $url = config('agent.base_url') . '/uploads/' . $product->is_image;

                $msg = $product->name
                    . PHP_EOL
                    . 'Code: ' . $product->code
                    . PHP_EOL
                    . 'Price: BDT ' . $product->offer_price
                    . PHP_EOL
                    . $product->detail;

                $sRes = FacebookAPI::postPhotoAsPage(
                    [
                        'message' => $msg,
                        'url' => $url,
                        'published' => false
                    ],
                    $this->agent);

//                Log::info("Unpublished post response: " . print_r($sRes, true));

                if ($sRes['error'] == false && isset($sRes['data']['id'])) {
                    array_push(
                        $this->mediaFbIds,
                        [
                            "media_fbid" => $sRes['data']['id']
                        ]
                    );
                }

                usleep(100000);
            }

//            Log::info("Photo post ids " . print_r($this->mediaFbIds, true));

            if ($this->mediaFbIds) {
                $sRes = FacebookAPI::postFeedAsPage(
                    [
                        'message' => $this->message,
                        'attached_media' => (array)$this->mediaFbIds
                    ],
                    $this->agent
                );

                Log::info("Bulk Facebook post response: " . print_r($sRes, true));
            }
        } catch( Exception $e ) {
            throw $e;
        }
    }

    public function failed(Exception $exception) {
        Log::info('Job failed due to ' . $exception->getMessage());
    }
}
