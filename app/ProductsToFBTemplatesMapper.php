<?php

namespace App;


use Illuminate\Support\Facades\Log;

class ProductsToFBTemplatesMapper
{
    public static function cardsDataArray($products, $entityName, $buyButtonTitle = 'Add to Cart') {
        $cardsData = [];

        if ($products) {
            foreach($products as $product) {

                $varEntityName = '';
                if (!$entityName && $product->entityName) {
                    $varEntityName = $product->entityName;
                }

                $img_url = null;
                if ($product->is_image)
                    $img_url = config('agent.base_url') . '/uploads/' . $product->is_image;
                else if ($product->image_link)
                    $img_url = $product->image_link;
                else
                    $img_url = config('agent.base_url') . '/images/img_unavailable.png';

                $dataArray = [
                    "title" => $product->name,
                    "image_url" => $img_url,
                    "subtitle" => $product->detail ? $product->detail : 'No detail available!',
                    "redirect_url" => $product->external_link ? $product->external_link : null,
                    "buttons" => []
                ];

                $buttonTitle = $buyButtonTitle;

                if ($buyButtonTitle) {
                    if (strlen($product->detail) > config('agent.facebook_protocols.desc_max_length')) {
                        $dataArray["buttons"][] = [
                            "button_title" => 'More Detail',
                            "button_payload" => config('agent.facebook_protocols.payload_product_detail') . $product->id
                        ];
                    }

                    if ($buyButtonTitle == '#price') {
                        $buttonTitle = "\xF0\x9F\x9B\x92" . ' (' . $product->offer_price . ' TK)';
                    }

                    $dataArray["buttons"][] = [
                        "button_title" => $buttonTitle,
                        "button_payload" => $entityName ? $entityName . '-' . $product->code : $varEntityName . '-' . $product->code
                    ];
                }

                array_push($cardsData, $dataArray);
            }
        }

        return $cardsData;
    }

    public static function cardTemplateForAddToCartConfirmation($entity, $attributes) {

        $templateArray = [];

        if ($entity) {

            $subtitle = null;

            if ($attributes && count($attributes) > 0) {

                $subtitle = 'Detail: ';

                foreach ($attributes as $attribute) {
                    $subtitle = $subtitle . "{$attribute['name']}: ";
                    $subtitle = $subtitle . "{$attribute['value']} ";
                }
            }

            $img_url = null;
            if ($entity->is_image)
                $img_url = config('agent.base_url') . '/uploads/' . $entity->is_image;
            else if ($entity->image_link)
                $img_url = $entity->image_link;

            $templateArray[] = [
                "title" => $entity->name . ' in Usha Bag!',
                "image_url" => $img_url,
                "subtitle" => $subtitle,
                "buttons" => [
                    [
                        "type" => "web_url",
                        "url" => "https://usha.ulkabd.com/webview/cart",
                        "title" => "Show Cart",
                        "webview_height_ratio" => "full",
                        "messenger_extensions" => true
                    ],
                    [
                        "type" => "postback",
                        "payload" => "Start Browsing",
                        "title" => "Continue Browsing"
                    ]
                ]
            ];
        }

        return $templateArray;
    }
}
