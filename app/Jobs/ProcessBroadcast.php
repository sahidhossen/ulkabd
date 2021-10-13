<?php

namespace App\Jobs;

use App\Category;
use App\FacebookAPI;
use App\FacebookResponseTypes;
use App\Products;
use App\ProductsToFBTemplatesMapper;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $message;
    public $product_ids;
    public $category_id;
    public $agent;
    public $broadcast;

    public function __construct($message, $agent, $broadcast, $product_ids = null, $category_id = null)
    {
        $this->message = $message;
        $this->agent = $agent;
        $this->broadcast = $broadcast;
        $this->product_ids = $product_ids;
        $this->category_id = $category_id;
    }

    /**
     * @return array|null
     */
    private function productsCreative() {
        try {
            if (!$this->product_ids) return null;

            $products = Products::whereIn('id', $this->product_ids )->get();

            if (!$products) return null;

            $categories = [];
            foreach($products as $product) {
                $category = null;
                if (isset($categories[$product->category_id])) {
                    $category = $categories[$product->category_id];
                }
                if (!$category) {
                    $category = Category::find($product->category_id);
                    $categories[$product->category_id] = $category;
                }

                if ($category) $product->entityName = $category->apiai_entity_name;
                else $product->entityName = '';
            }

            $productsCreative = FacebookResponseTypes::genericCardsWith(
                ProductsToFBTemplatesMapper::cardsDataArray(
                    $products,
                    null,
                    '#price'
                )
            );

            return $productsCreative;
        } catch( Exception $e ) {
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        try {
            $creative = [
                [
                    'text' => $this->message
                ]
            ];

            $pCreative = $this->productsCreative();
            if ($pCreative) array_push($creative, $pCreative);

            Log::info("Creative:");
            Log::info(print_r($creative, true));

            foreach($creative as $cMessage) {
                usleep(100000);
                $creativeResponse = FacebookAPI::createMessageCreative(
                    [$cMessage],
                    $this->agent
                );

//                Log::info('Create creative:');
//                Log::info($creativeResponse);

                if (isset($creativeResponse['data']['message_creative_id'])) {
                    usleep(100000);
                    $broadcastResponse = FacebookAPI::sendBroadcast($this->agent, $creativeResponse['data']['message_creative_id']);
//                $this->broadcast->ext_creative_id = $creativeResponse['data']['message_creative_id'];

//                    Log::info('Broadcast:');
//                    Log::info($broadcastResponse);
                }
            }

            $this->broadcast->state = 1;
            $this->broadcast->save();

            usleep(100000);
            dispatch(
                (new BroadcastReachEstimate(
                    $this->agent,
                    $this->broadcast
                ))
                    ->onQueue(
                        config('queueNames.messenger_updater')
                    )
            );
        } catch( Exception $e ) {
            throw $e;
        }
    }

    public function failed(Exception $exception) {
        Log::info('Job failed due to ' . $exception->getMessage());
    }
}
