<?php

namespace App\Http\Controllers;

use App\Agents;
use App\Broadcast;
use App\Jobs\ProcessBroadcast;
use App\Products;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class BroadcastController extends Controller
{
    use DispatchesJobs;

    public function __construct() {
        $this->middleware('auth');
    }

    public function broadcastProducts(Request $request) {
        try {
            $agent = Agents::getCurrentAgent();
            if( $agent == null )
                throw new Exception("Authentication error!");
            elseif(!$agent->page_subscription)
                throw new Exception("Please connect your facebook page before broadcasting entities.");

//            Log::info("Request: " . print_r($request->all(), true));

            $product_ids = json_decode($request->input('product_ids'));
            $message = $request->input('message');

            if (!$product_ids || !$message)
                throw new Exception("Invalid argument error!");

            $broadcast = $this->create( $message, $product_ids );

            if( $broadcast == null ) {
                throw new Exception("Broadcast creation error!");
            }

            dispatch(
                (new ProcessBroadcast(
                    $message,
                    $agent,
                    $broadcast,
                    $product_ids
                ))
                    ->onQueue(
                        config('queueNames.messenger_updater')
                    )
            );

            return [
                'error' => false,
                'message' => "Entities being broadcasted, Get reach estimation from 'Broadcast' panel."
            ];
        }catch(Exception $e) {
            return ['error'=>true, 'message'=>$e->getMessage() ];
        }
    }

    /*
     * Store broadcast message
     */
    private function create( $message, $products ){
        try {
            $agent = Agents::getCurrentAgent();

            $broadcast = new Broadcast();
            $broadcast->agent_id = $agent->id;
            $broadcast->creative = json_encode([
                'text' => $message,
                'products' => $products
            ]);
            if($broadcast->save()){
                return $broadcast;
            }
            return null;
        }catch(Exception $e){
            throw $e;
        }


    }
}
