<?php

namespace App\Http\Controllers;

use App\BotExtensionModels\ProfileCart;
use App\CartRedisCache;
use App\EndUser;
use App\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class ProfileCartController extends Controller
{
    /*
     * Profile + Cart object
     * @return array
     *
     * web/profile_cart
     */
    public function profile_cart(Request $request)
    {
        try {
            Log::info("profile_cart request received");

            $header = $request->header('usha-app-secret');
            $appSecret = config('app.key');

            if (!isset($header) || (isset($header) && strcmp($header, $appSecret) !== 0)) {
                throw new Exception("Authentication failed. Invalid header.");
            }

            $external_id = $request->input('external_id');
            $extension_platform = $request->input('extension_platform');

            if (isset($extension_platform) && isset($external_id)) {
                switch ($extension_platform) {
                    case 'facebook': {
                        return [
                            'error'=> false,
                            'data' => (new ProfileCart($external_id, $extension_platform))->data(),
                            'message' => 'Success'
                        ];
                    }
                    break;

                    default:
                        throw new Exception("No support for this platform yet implemented.");
                }
            }
            else {
                throw new Exception("Invalid external user id ".$external_id);
            }

        }catch( Exception $e ) {
            $error = true;
            if ($e->getCode() === 200) {
                $error = false;
            }

            return [
                'error'=> $error,
                'data' => null,
                'message'=>$e->getMessage()
            ];
        }

    }

    /**
     * @param Request $request
     * @return array
     */
    public function cart_update_checkout(Request $request) {
        try {
            Log::info("cart_update_checkout request received");
//            Log::info(print_r($request->all(), true));

            $header = $request->header('usha-app-secret');
            $appSecret = config('app.key');

            if (!isset($header) || (isset($header) && strcmp($header, $appSecret) !== 0)) {
                throw new Exception("Authentication failed. Invalid header.");
            }

            $external_id = $request->input('external_id');
            $extension_platform = $request->input('extension_platform');

            if (isset($extension_platform) && isset($external_id)) {
                switch ($extension_platform) {
                    case 'facebook': {

                        $entities = $request->input('entities');
                        $newCount = count($entities);

                        $cachedCart = CartRedisCache::getCart($external_id);
                        $existingCartData = $cachedCart->getData();
                        $oldCount = count($existingCartData['entities']);

                        if (isset($entities) && $newCount >= 0 && $oldCount > 0) {

                            if ($newCount > 0) {
                                $isDropped = $cachedCart->updateEntitySet($entities);

                                if ($isDropped == false) {
                                    $is_checkedout = $request->input('is_checkedout');

                                    if (isset($is_checkedout) && $is_checkedout == true) {
    
                                        $order_time = $request->input('order_time');
                                        $expected_delivery_time = $request->input('expected_delivery_time');
    
                                        $status_detail = [
                                            'order_time' => null,
                                            'expected_delivery_time' => null
                                        ];
    
                                        if ($order_time)
                                            $status_detail['order_time'] = $order_time;
                                        if ($expected_delivery_time)
                                            $status_detail['expected_delivery_time'] = $expected_delivery_time;
    
                                        $success = Orders::createOrder($cachedCart, $external_id, $status_detail);
                                        if ($success == true) $cachedCart->checkOut();
                                    }
                                }
                            }
                            else {
                                $cachedCart->drop();
                            }

                            return [
                                'error'=> false,
                                'data' => null,
                                'message' => 'Success'
                            ];
                        }
                        else {
                            throw new Exception('Invalid entities data!');
                        }

                    }
                        break;

                    default:
                        throw new Exception("No support for this platform yet implemented.");
                }
            }
            else {
                throw new Exception("Invalid external user id");
            }

        }catch( Exception $e ) {
            return [
                'error'=>true,
                'data' => null,
                'message'=>$e->getMessage()
            ];
        }
    }


    /**
     * @param Request $request
     * @return array
     */
    public static function user_profile(Request $request) {
        try {

            Log::info("user_profile request received");

            $header = $request->header('usha-app-secret');
            $appSecret = config('app.key');

            if (!isset($header) || (isset($header) && strcmp($header, $appSecret) !== 0)) {
                throw new Exception("Authentication failed. Invalid header.");
            }

            $external_id = $request->input('external_id');
            $extension_platform = $request->input('extension_platform');

            if (isset($extension_platform) && isset($external_id)) {
                switch ($extension_platform) {
                    case 'facebook': {

                        EndUser::updateProfile($request->all());

                        return [
                            'error'=> false,
                            'data' => $request->all(),
                            'message' => 'Success'
                        ];
                    }
                        break;

                    default:
                        throw new Exception("No support for this platform yet implemented.");
                }
            }
            else {
                throw new Exception("Invalid extension_platform or external_id");
            }

        }catch( Exception $e ) {
            return [
                'error'=>true,
                'data' => null,
                'message'=>$e->getMessage()
            ];
        }
    }
}
