<?php

namespace App\Http\Controllers;

use App\EndUser;
use App\FacebookAPI;
use App\Jobs\OrderStatusUpdate;
use App\Orders;
use App\StandardFacebookResponses;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use App\Agents;

class OrdersController extends Controller
{
    use DispatchesJobs;

    /**
     * OrdersController constructor.
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('orders');
    }

    /*
     * Get all new orders based on the
     * ReactJS
     * Route->API
     */
    public function get_order_by(Request $request) {
        try {
            $user = Auth::user();
            $agent_code = Redis::get('agent_code_'.$user->id);
            $current_agent = Agents::where('agent_code',$agent_code )->first();

            if ($current_agent == null) throw new Exception('Could not find agent!');

            $state = $request->input('state');
            $state  = ( $state == '-1' ) ? null : $state;
            $data = Orders::active_orders($current_agent->id, $state);

            $response = [
                'error' => false,
                'data' => $data,
                'message' => 'Success'
            ];

//            Log::info(print_r($response, true));

            return $response;
        }
        catch( Exception $e ) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function current_order(Request $request){
        try {
            $order_id = $request->input('id');
            $current_order = Orders::find( $order_id );

//            Log::info(print_r($current_order, true));

            return [
                'error'=>false,
                'order'=>$current_order
            ];
        }
        catch( Exception $e ) {
            return ['error'=>true, 'message'=>$e->getMessage()];
        }
    }

    public function order_action( Request $request) {
        try {
//            Log::info('Order state change request');

            $state = $request->input('state');
            $orderId = $request->input('order_id');
            $msg = $request->input('msg');

            if ($state == null || $orderId == null) throw new Exception("Invalid request");

            $order = Orders::find($orderId);

            if ($order == null) throw new Exception("Invalid order_id");

            $order->status = $state;
            $order->save();

            $agent = Agents::find($order->agent_id);
            $user = EndUser::find($order->end_user_id);

            $msgTitle = 'Your order Updated!';
            $message = 'Order Code: ' . $order->order_code . '. ';
            switch($state) {
                case 0: {
                    $msgTitle = 'Order is NEW';
                    $message = $message . ($msg !== 'null' ? $msg : 'Your order state is changed to NEW');
                }
                break;

                case 1: {
                    $msgTitle = 'Order DELIVERED';
                    $message = $message . ($msg !== 'null' ? $msg : 'Your order is DELIVERED');
                }
                    break;

                case 2: {
                    $msgTitle = 'Order SENT';
                    $message = $message . ($msg !== 'null' ? $msg : 'Your order is SENT');
                }
                    break;

                case 3: {
                    $msgTitle = 'Order CANCELLED';
                    $message = $message . ($msg !== 'null' ? $msg : 'Your order is CANCELLED');
                }
                    break;
                
                case 4: {
                    $msgTitle = 'Order CONFIRMED';
                    $message = $message . ($msg !== 'null' ? $msg : 'Your order is CONFIRMED');
                }
                    break;
            }

            dispatch(
                (new OrderStatusUpdate(
                    $user->agent_scoped_id,
                    $msgTitle,
                    $message,
                    $agent
                ))
                    ->onQueue(
                        config('queueNames.messenger_updater')
                    )
            );

            return [
                'error'     => false,
                'message'   => 'Success',
                'state'     => $state
            ];
        }
        catch( Exception $e ) {
            return ['error'=>true, 'message'=>$e->getMessage()];
        }
    }

}
