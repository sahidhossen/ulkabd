<?php

namespace App;

use App\BotExtensionModels\Cart;
use App\BotExtensionModels\Profile;
use App\Events\BroadcastNewOrder;
use App\Jobs\OrderStatusUpdate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class Orders extends Model
{
    use DispatchesJobs;

    protected $table = "orders";

    protected $fillable = [
        'agent_id',
        'end_user_id',
        'delivery_charge',
        'status',
        'order_code',
        'status_detail',
        'payment_status',
        'entities'
    ];

    private static function getEntityModel($entity, $attributes, $quantity) {

        $img_url = null;
        if ($entity->is_image)
            $img_url = config('agent.base_url') . '/uploads/' . $entity->is_image;
        else if ($entity->image_link)
            $img_url = $entity->image_link;

        $data = [
            'id' => $entity->id, // Entity id
            'name' => $entity->name, // Name of the entity
            'img_url' => $img_url, // Entity image url
            'unit_price' => (float)$entity->offer_price, // Entity unit price
            'quantity' => $quantity, // Number of entities
            'total_price' => (float)$quantity * $entity->offer_price, // Unit price multiplied by quantity
            'attributes' => $attributes,
            'entity_code' => $entity->code, // Merchant given entity identification code
            'available_quantity' => $entity->stock <= -1 ? 100 : min(100, $entity->stock), // Number of sellable entities, null (depends on is_available)/ >= 0 (is_available = true)
            'is_available' => ($entity->stock > 0 || $entity->stock <= -1) ? true : false // Is entity available, true / false
        ];

        return $data;
    }

    private static function orderDetail($orderData, $agent) {
        $order_number = $orderData['order_id'];
        $cart_items = $orderData['entities'];
        $total_quantity = 0;
        $total_price = 0.0;
        $entities = [];
        $cart_name = $agent->fb_page_name;
        $agent_name = $agent->agent_name;

        foreach ($cart_items as $item) {
            $entity_id = $item['id'];
            $attributes = $item['attributes'];

            $quantity = $item['quantity'];
            $entity = Products::find($entity_id);

            if (isset($entity)) {
                $entity_model = Orders::getEntityModel($entity, $attributes, $quantity);
                $entities[] = $entity_model;

                $total_quantity += $entity_model['quantity'];
                $total_price += (float)$entity_model['total_price'];
            }
        }

        $data = [
            'total_price' => $total_price, // Sum of prices of all entities in cart
            'order_number' => $order_number, // Unique order id created by system
            'total_quantity' => $total_quantity, // Total number of entities in cart
            'entities' => $entities,
            'max_order_quantity' => 100, // Maximum quantity of an entity purchaseable at a time
            'is_checkedout' => false, // Is cart checked out and order confirmed
            'is_cart_editable' => true, // Is entities in cart updateable
            'order_status' => 0, // null / business stated order status

            'page_title' => 'Usha Bag', // Cart view Title, e.g => Usha Bag or Usha Order
            'cart_name' => $cart_name, // Cart name given by us
            'generic_entity_name' => 'Product', // Generic entity name given by business. e.g => Product, Rooms
            'currency' => 'BDT', // Currency name, e.g => BDT, USD
            'currency_sign' => '৳', // Currency sign, e.g => ৳, $

            'business_name' => $agent_name, // Name of the business or merchant
            'external_business_id' => $agent->fb_page_id, // external platform scoped business id
            'business_img_url' => $agent->image_path // business image url / null
        ];

        return $data;
    }

    /*
     * get all active order lists
     * @return array
     */
    public static function active_orders( $agent_id, $state ){
        try {
            $agent = Agents::find($agent_id);

            $where = ['agent_id' => $agent_id];

            // by state
            if (isset($state) &&
                $state >= config('agent.delivery_state.new') &&
                $state <= config('agent.delivery_state.confirmed')) {
                $where['status'] = $state;
            }

            $data = [];

            $all_orders = Orders::where($where)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach($all_orders as $order) {
                $order->status_detail = $order->status_detail ?
                    json_decode($order->status_detail, true) :
                    [
                        'order_time' => null,
                        'expected_delivery_time' => null
                    ];

                $cart = [
                    'order_id' => $order->order_code,
                    'entities' => json_decode($order->entities, true)
                ];

                $data[] = [
                    'state'=> $state,
                    'order' => $order,
                    'profile' => EndUser::getProfile($order->end_user_id),
                    'cart' => Orders::orderDetail($cart, $agent)
                ];
            }

            return $data;
        }
        catch(Exception $e) {
            throw $e;
        }
    }

    /*
     * get all active order count
     * @return array
     */
    public static function active_orders_counter( $agent_id ){
        try {
            $all_orders = Orders::where([
                'agent_id' => $agent_id,
                'status' => 0,
            ])->count();

            return $all_orders;

        }
        catch(Exception $e) {
            return 0;
        }
    }

    public static function createOrder(CartRedisCache $cart, $external_id, $status_detail) {
        try {
            $end_user = EndUser::where(['agent_scoped_id' => $external_id])->first();
            if (!isset($end_user)) {
                throw new Exception("Invalid user!");
            }
            if (!isset($end_user->address)) {
                throw new Exception("User is not authenticated. Can't place an order. Please authenticate first!");
            }

            $agent = Agents::find($end_user->agent_id);
            if (!isset($agent)) {
                throw new Exception("Invalid merchant!");
            }

            $cartData = $cart->getData();
            $entities = $cartData['entities'];
            $onlyRequiredDataSetEntities = [];
            $totalQuantity = 0;

            $outOfStockProducts = [];
            $sufficientProducts = [];

            foreach($entities as $entity) {
//                Log::info($entity);

                $product = Products::find((int)$entity['id']);

                if (isset($product) && $product->stock != -1) {

                    if ($product->stock >= 0 && $product->stock < (int)$entity['quantity']) {
                        $outOfStockProducts[] = $product;
                    }
                    else {
                        $product->stock = max( 0, ($product->stock - (int)$entity['quantity']) );
                        $sufficientProducts[] = $product;
                    }
                    
                }

                $onlyRequiredDataSetEntities[] = [
                    'id' => $entity['id'],
                    'attributes' => $entity['attributes'],
                    'quantity' => $entity['quantity']
                ];

                $totalQuantity += (int)$entity['quantity'];
            }

            if (count($outOfStockProducts) > 0) {
                $productNames = 'Insufficient ';
                foreach($outOfStockProducts as $product) {
                    $productNames .= ($product->name . ', ');
                }
                $msg = $productNames . " please review cart and try again.";

                dispatch(
                    (new OrderStatusUpdate(
                        $external_id,
                        // If change in 'Order Placing Failed!' change also in StandardFacebookResponses.php
                        'Order Placing Failed!',
                        $msg,
                        $agent
                    ))
                        ->onQueue(
                            config('queueNames.messenger_updater')
                        )
                );

                return false;
            }
            else {
                foreach($sufficientProducts as $product) {
                    $product->save();
                }
            }

            $order = new Orders();
            $order->agent_id = $agent->id;
            $order->end_user_id = $end_user->id;
            $order->delivery_charge = 0;
            $order->status = config('agent.delivery_state.new');
            $order->order_code = $cartData['order_id'];
            $order->payment_status = config('agent.payment.due');
            $order->entities = json_encode($onlyRequiredDataSetEntities);
            $order->status_detail = json_encode($status_detail);
            $order->save();

            dispatch(
                (new OrderStatusUpdate(
                    $external_id,
                    'Order Placed Successfully!',
                    'Order Code: ' . $cartData['order_id'] . '. Total items: ' . $totalQuantity . '. Your order is being processed.',
                    $agent
                ))
                    ->onQueue(
                        config('queueNames.messenger_updater')
                    )
            );

            $data = [];

            $order->status_detail = $status_detail ?
                $status_detail :
                [
                    'order_time' => null,
                    'expected_delivery_time' => null
                ];

            $cart = [
                'order_id' => $order->order_code,
                'entities' => json_decode($order->entities, true)
            ];

            $data[] = [
                'state'=> config('agent.delivery_state.new'),
                'order' => $order,
                'profile' => EndUser::getProfile($order->end_user_id),
                'cart' => Orders::orderDetail($cart, $agent)
            ];

            event(
                new BroadcastNewOrder(
                    $data,
                    $agent
                )
            );

            return true;

        } catch(Exception $e) {
            throw $e;
        }
    }
}
