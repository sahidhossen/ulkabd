<?php

namespace App;

use League\Flysystem\Exception;

class StandardFacebookResponses
{
    public static function addToCartConfirmationCardWith($entity, $attributes) {

        if (isset($entity) && isset($attributes)) {
            $model = [
                "attachment" => [
                    "type" => "template",
                    "payload" => [
                        "template_type" => "generic",
                        "elements" => []
                    ]
                ]
            ];

            $elements = ProductsToFBTemplatesMapper::cardTemplateForAddToCartConfirmation($entity, $attributes);

            $model['attachment']['payload']['elements'] = $elements;

            return $model;
        }
        else
            return null;
    }

    public static function orderStatusMessage($title, $subtitle, $btn_title) {
        try {
            $model = [
                "attachment" => [
                    "type" => "template",
                    "payload" => [
                        "template_type" => "generic",
                        "elements" => [
                            [
                                "title" => $title,
                                "image_url" => null,
                                "subtitle" => $subtitle,
                                "buttons" => [
                                    [
                                        "type" => "postback",
                                        "payload" => "Start Browsing",
                                        "title" => $btn_title
                                    ],
                                    [
                                        "type" => "web_url",
                                        "url" => "http://m.me/UshaAI",
                                        "title" => "Powered by Usha"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            // 'Order Placing Failed!' in Orders.php createOrder()
            // If change in createOrder() change also here
            // TODO: Need a better solution in adding this button condition
            if ($title === 'Order Placing Failed!') {
                array_unshift(
                    $model['attachment']['payload']['elements'][0]['buttons'],
                    [
                        "type" => "web_url",
                        "url" => "https://usha.ulkabd.com/webview/cart",
                        "title" => "Show Cart",
                        "webview_height_ratio" => "full",
                        "messenger_extensions" => true
                    ]
                );
            }

            return $model;

        } catch(Exception $e) {
            throw $e;
        }
    }
}
