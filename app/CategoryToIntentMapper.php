<?php

namespace App;

use Illuminate\Support\Facades\Log;

class CategoryToIntentMapper {

    public static function cardsDataArray($subs) {
        $cardsData = [];

        if ($subs) {
            foreach($subs as $subCategory) {

                if ($subCategory->image == null)
                    return null;

                $dataArray = [
                    "title" => $subCategory->name,
                    "image_url" => config('agent.base_url') . '/uploads/' . $subCategory->image,
                    "subtitle" => $subCategory->description,
                    "redirect_url" => $subCategory->external_link ? $subCategory->external_link : null,
                    "buttons" => [
                        [
                            "button_title" => $subCategory->name,
                            "button_payload" => $subCategory->name
                        ]
                    ]
                ];

                array_push($cardsData, $dataArray);
            }
        }

        return $cardsData;
    }

    public static function quickRepliesDataArray($subs) {
        $replies = [];

        if ($subs) {
            foreach($subs as $subCategory) {
                $dataArray = [
                    'title' => $subCategory->name,
                    'payload' => $subCategory->name
                ];

                array_push($replies, $dataArray);
            }
        }

        return $replies;
    }

    private static function actionWith($name, $dType, $prompt) {
        return [
            "required" => true,
            "dataType" => $dType,
            "name" => $name,
            "value" => "$" . $name,
            "prompts" => [
                $prompt
            ],
            "isList" => false
        ];
    }

    public static function actionSlots($category) {

        if ($category) {
            $dataArray = [];

            $chooseProductAction  = CategoryToIntentMapper::actionWith(
                $category->apiai_entity_name,
                "@" . $category->apiai_entity_name,
                "Please choose a product:"
            );

            array_push($dataArray, $chooseProductAction);

            $attStr = $category->required_attributes;

            if (isset($attStr) && strlen($attStr) > 0) {
                $attArray = explode(',', $attStr);

                foreach($attArray as $att) {
                    $param = mb_strtolower($att);
                    $param = preg_replace('/\s+/', '-', $param);
                    $param = preg_replace('/-+/', '-', $param);

                    $attAction = CategoryToIntentMapper::actionWith(
                        $param,
                        "@sys.any",
                        ucfirst($att . ':')
                    );

                    array_push($dataArray, $attAction);
                }
            }

//            // add quantity
//            array_push(
//                $dataArray,
//                CategoryToIntentMapper::actionWith(
//                    'quantity',
//                    "@sys.number-integer",
//                    "How many you would like to buy?"
//                )
//            );
//
//            // add phone
//            array_push(
//                $dataArray,
//                CategoryToIntentMapper::actionWith(
//                    'phone',
//                    "@sys.phone-number",
//                    "Contact number:"
//                )
//            );
//
//            // add address
//            array_push(
//                $dataArray,
//                CategoryToIntentMapper::actionWith(
//                    'address',
//                    "@sys.address",
//                    "Address (format: house - X, road - Y, Mirpur - Z, Dhaka):"
//                )
//            );
//
//            // add confirmation
//            array_push(
//                $dataArray,
//                CategoryToIntentMapper::actionWith(
//                    'confirmation',
//                    "@order-confirmation",
//                    "Please confirm your order."
//                )
//            );

            return $dataArray;
        }

        return null;
    }

    public static function userSaysDataArray($category, Array $says = null, $productCode = null) {

        if ($category) {
            $dataArray = [
                [
                    'isTemplate' => false,
                    'count' => 0,
                    'data' => [
                        [
                            'text' => $category->name
                        ]
                    ]
                ]
            ];

            if ($says) {
                foreach($says as $say) {
                    array_push(
                        $dataArray,
                        [
                            'isTemplate' => false,
                            'count' => 0,
                            'data' => [
                                [
                                    'text' => $say
                                ]
                            ]
                        ]
                    );
                }
            }

            if ($productCode) {
                array_push(
                    $dataArray,
                    [
                        'isTemplate' => false,
                        'count' => 0,
                        'data' => [
                            [
                                'text' => $productCode,
                                "alias" => $category->apiai_entity_name,
                                "meta" => "@" . $category->apiai_entity_name,
                                "userDefined" => false
                            ]
                        ]
                    ]
                );
            }

            return $dataArray;
        }

        return null;
    }

}
