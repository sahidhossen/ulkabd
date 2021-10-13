<?php

namespace App\BotExtensionModels;

use App\Agents;
use App\CartRedisCache;
use App\EndUser;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class ProfileCart
{
    private $external_id;
    private $platform;
    private $data;
    private $end_user;
    private $redis_cart;
    private $agent;

    public function __construct($external_id, $platform) {
        $this->external_id = $external_id;
        $this->platform = $platform;
    }

    public function data() {
        try {
            Log::info("user id: " . $this->external_id);
            Log::info("platform: " . $this->platform);

            $this->end_user = EndUser::where(['agent_scoped_id' => $this->external_id])->first();
            if (!isset($this->end_user)) {
                throw new Exception("Invalid user!");
            }

            $this->agent = Agents::find($this->end_user->agent_id);
            if (!isset($this->agent)) {
                throw new Exception("Invalid merchant!");
            }

//            Log::info("Agent: ");
//            Log::info(print_r($this->agent, true));

            $cachedCart = CartRedisCache::getCartCacheData($this->external_id);
            $this->redis_cart = $cachedCart->getData();
            if (!isset($this->redis_cart)) {
                throw new Exception("You have no items in Cart.", 200);
            }

//            Log::info("Cart data:");
//            Log::info(print_r($this->redis_cart, true));

            $this->initiateData();

            return $this->data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function initiateData() {
        $this->data = [
            'authenticated' => isset($this->end_user->address) ? true : false, // true / false

            'user' =>
                (new UshaUser($this->external_id,
                    $this->platform,
                    $this->end_user))
                    ->userData(), // Generic user data / null

            'home' => [], // home view data / null

            'profile' =>
                (new Profile($this->end_user))
                    ->profileData(), // profile view data - nonnull

            'cart' =>
                (new Cart($this->redis_cart,
                    $this->agent))
                    ->cartData(), // cart view data / null

            'view' => 'cart' // View to show. Possible values: 'cart' / 'home' / 'login_signup'
        ];
    }
}
