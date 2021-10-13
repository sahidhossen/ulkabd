<?php

namespace App;

use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CartRedisCache
{
    private $agent_scoped_id;
    private $key;
    private $cart;
    private $timeOut;

    private function __construct($agent_scoped_end_user_id) {
        $this->agent_scoped_id = $agent_scoped_end_user_id;
        $this->key = 'cart-'.$this->agent_scoped_id;
        $this->timeOut = (60/*sec*/ * 60/*min*/ * 24/*hr*/ * 3/*day*/);
    }

    public static function getCart($agent_scoped_end_user_id) {
        $cart = new CartRedisCache($agent_scoped_end_user_id);
        $cart->cartModel(true);
        return $cart;
    }

    public static function getCartCacheData($agent_scoped_end_user_id) {
        $cart = new CartRedisCache($agent_scoped_end_user_id);
        $cart->cartModel();
        return $cart;
    }

    public function insertEntity($entity_id, Array $attributes, $quantity) {

        $count = count($this->cart['entities']);
        $merged = false;

        if ($count > 0) {
            $found = true;
            for ($j = 0; $j < $count; ++$j) {
                $entity = $this->cart['entities'][$j];
                if ((int)$entity['id'] === (int)$entity_id) {
                    $refAtt = $entity['attributes'];
                    for ($i = 0; $i < count($refAtt) && $i < count($attributes); ++$i) {
                        if (strcasecmp($refAtt[$i]['value'], $attributes[$i]['value']) !== 0) {
                            $found = false;
                        }
                    }
                    if ($found === true) {
                        $this->cart['entities'][$j]['quantity'] = (int)$entity['quantity'] + $quantity;
                        $merged = true;
                    }
                }
            }
        }

        if ($merged === false) {
            array_unshift(
                $this->cart['entities'],
                [
                    'id' => $entity_id,
                    'attributes' => $attributes,
                    'quantity' => $quantity
                ]
            );
        }

//        $this->cart['entities'][] = [
//            'id' => $entity_id,
//            'attributes' => $attributes,
//            'quantity' => $quantity
//        ];

        $this->saveData();
    }

    /*
     * Update cart cache with new entities data. Returns a boolean indicating if card is dropped.
     * @return boolean
     */
    public function updateEntitySet($entities) {

        $newEntities = [];

        foreach($entities as $entity) {
            if (isset($entity) && (int)$entity['quantity'] > 0) {
                $newEntities[] = $entity;
            }
        }

        if (count($newEntities) > 0) {
            $this->cart['entities'] = $newEntities;
            $this->saveData();
            return false;
        }
        else {
            $this->drop();
            return true;
        }
    }

    public function getData() {
        return $this->cart;
    }

    public function checkOut() {
        $this->deleteData();
    }

    public function drop() {
        $this->deleteData();
    }

    private function cartModel($initiate = false) {
        $cartData = Redis::get($this->key);

        if ($cartData) {
            $this->cart = unserialize($cartData);
            $this->updateOrderId();
            Redis::expire($this->key, $this->timeOut);
        }
        else if ($initiate === true) {
            $this->cart = [
                'order_id' => $this->generateOrderId(),
                'entities' => []
            ];
        }
    }

    private function generateOrderId() {
        $date = (new DateTime('now', new DateTimeZone('Asia/Dhaka')))->format('Ymd');
        return $date . strtoupper(substr(uniqid(sha1(time())),0,4));
    }

    public function updateOrderId() {
        $date = (new DateTime('now', new DateTimeZone('Asia/Dhaka')))->format('Ymd');
        // Log::info("Date:");
        // Log::info($date);
        if (substr($date, -2, 2) != substr($this->cart['order_id'], -6, 2)) {
            // Log::info("changing order code");
            $this->cart['order_id'] = substr_replace( $this->cart['order_id'], $date, 0, 8 );
            $this->saveData();
        }
    }

    private function saveData() {
        Redis::setEx($this->key, $this->timeOut, serialize($this->cart));
    }

    private function deleteData() {
        Redis::del($this->key);
    }
}
