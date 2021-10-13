<?php

namespace App;

use Illuminate\Support\Facades\Log;

class FacebookResponseTypes {

    private static function quickRepliesTemplatesFrom($titlePayloadArray) {
        if ($titlePayloadArray && count($titlePayloadArray) > 0) {
            $buttons = [];

            foreach($titlePayloadArray as $titlePayload) {
                $button = [
                    'content_type' => 'text',
                    'title' => $titlePayload['title'],
                    'payload' => $titlePayload['payload']
                ];

                array_push($buttons, $button);
            }

            return $buttons;
        }
        else
            return null;
    }

    /**
     * @param $message
     * @param $titlePayloadArray
     * @return array|null
     */
    public static function quickRepliesWith($message, $titlePayloadArray) {
        if ($titlePayloadArray && count($titlePayloadArray) > 0) {

            $buttons = FacebookResponseTypes::quickRepliesTemplatesFrom($titlePayloadArray);

            if ($message !== null) {
                return [
                    'text' => $message,
                    'quick_replies' => $buttons
                ];
            }
            else {
                return [
                    'quick_replies' => $buttons
                ];
            }
        }
        else
            return null;
    }

    private static function cardTemplatesFrom($dataArray) {
        if ($dataArray && count($dataArray) > 0) {
            $elementsArray = [];

            foreach($dataArray as $dataum) {
                $element = [
                    "title" => $dataum['title'],
                    "image_url" => isset($dataum['image_url']) ? $dataum['image_url'] : null,
                    "subtitle" => isset($dataum['subtitle']) ? $dataum['subtitle'] : null,
//                    "buttons" => [
//                        [
//                            "type" => "web_url",
//                            "url" => "https://petersfancybrownhats.com",
//                            "title" => "View Website"
//                        ],
//                    ]
                ];

                $redirect_url = null;
                if (isset($dataum['redirect_url']))
                    $redirect_url = $dataum['redirect_url'];
                else if (isset($dataum['image_url']))
                    $redirect_url = $dataum['image_url'];

                if ($redirect_url) {
                    $element["default_action"] = [
                        "type" => "web_url",
                        "url" => $redirect_url,
//                        "messenger_extensions" => false,
                        "webview_height_ratio" => "full",
//                        "fallback_url" => config('agent.base_url') . '/images/img_unavailable.png'
                    ];
                }

                if ($dataum["buttons"]) {
                    foreach($dataum["buttons"] as $button) {
                        $element['buttons'][] = [
                            "type" => "postback",
                            "title" => $button['button_title'],
                            "payload" => isset($button['button_payload']) ? $button['button_payload'] : null
                        ];
                    }
                }

//                if (isset($dataum['button_title'])) {
//                    $button = [
//                        "type" => "postback",
//                        "title" => $dataum['button_title'],
//                        "payload" => isset($dataum['button_payload']) ? $dataum['button_payload'] : null
//                    ];
//
////                    array_push($element['buttons'], $button);
//                    $element['buttons'] = [$button];
//                }

                array_push($elementsArray, $element);
            }

            return $elementsArray;
        }
        else
            return null;
    }

    public static function genericCardsWith($dataArray) {

        if ($dataArray && count($dataArray) > 0) {
            $model = [
                "attachment" => [
                    "type" => "template",
                    "payload" => [
                        "template_type" => "generic",
                        "elements" => []
                    ]
                ]
            ];

            $elements = FacebookResponseTypes::cardTemplatesFrom($dataArray);

            $model['attachment']['payload']['elements'] = $elements;

            return $model;
        }
        else
            return null;
    }

    public static function image($url) {
        if ($url !== null && filter_var($url, FILTER_VALIDATE_URL) !== FALSE) {
            $model = [
                "attachment" => [
                    "type" => "image",
                    "payload" => [
                        "url" => $url
                    ]
                ]
            ];

            return $model;
        }
        else
            return null;
    }

    private static function rssCardTemplatesFrom($dataArray, $fallbackURL) {
        if ($dataArray && count($dataArray) > 0) {
            $elementsArray = [];

            $i = 0;
            foreach($dataArray as $dataum) {
                if ($i >= 10) break;

                $element = [
                    "title" => $dataum['title'] ? $dataum['title'] : "Title NOT available!",
                    "image_url" => isset($dataum['image']) ? $dataum['image'] : config('agent.base_url') . '/images/img_unavailable.png',
                    "subtitle" => isset($dataum['description']) ? $dataum['description'] : null,
                ];

                $redirect_url = null;
                if (isset($dataum['link']))
                    $redirect_url = $dataum['link'];
                else if (isset($dataum['guid']))
                    $redirect_url = $dataum['guid'];

                if ($redirect_url) {
                    $element["default_action"] = [
                        "type" => "web_url",
                        "url" => $redirect_url,
                        "webview_height_ratio" => "full"
                    ];
                }

                $element['buttons'][] = [
                    "type" => "web_url",
                    "title" => "View on Web",
                    "url" => $redirect_url
                ];

                array_push($elementsArray, $element);
                $i++;
            }

            return $elementsArray;
        }
        else
            return null;
    }

    public static function rssResponseWith(Array $feed, $query) {
        $response = [];

        if ($feed['channel']['item']) {

//            $response[] = [
//                'text' => "You were looking for " . strtoupper($query)
//            ];

            if ($feed['channel']['description']) {
                $response[] = [
                    'text' => $feed['channel']['description']
                ];
            }

            $model = [
                "attachment" => [
                    "type" => "template",
                    "payload" => [
                        "template_type" => "generic",
                        "elements" => []
                    ]
                ]
            ];

            $elements = FacebookResponseTypes::rssCardTemplatesFrom($feed['channel']['item'], $feed['channel']['link']);

            $model['attachment']['payload']['elements'] = $elements;

            $response[] = $model;
        }

//        Log::info("Response");
//        Log::info($response);

        return $response;
    }

}
