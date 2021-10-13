<?php

namespace App;

use App\CurlAPI;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class FacebookAPI {

    public static function getLongLivedUserToken($appId, $appSecret, $shortLivedToken) {
        try {
            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api')
                . '/oauth/access_token?grant_type=fb_exchange_token&client_id=' . $appId
                . '&client_secret=' . $appSecret . '&fb_exchange_token=' . $shortLivedToken)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->get();

            $data = json_decode($response, true);

            if (isset($data['body']['error'])) {
                throw new Exception($data['body']['error']);
            }
            else {
                return [
                    'error' => false,
                    'data' => $data
                ];
            }
        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function verifyTokenAndGetPageInfo($accessToken) {
        try {
            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me?access_token=' . $accessToken)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->get();

            return json_decode($response, true);

        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function setMessengerProfile($agent, $page_access_token) {
        try {
            $data = [
                "whitelisted_domains" => [config('agent.base_url'), "http://m.me/UshaAI"],
                "get_started" => [
                    "payload" => config('agent.facebook_protocols.get_started_payload')
                ],
                "greeting" => [
                    [
                        "locale" => "default",
                        "text" => "Hello {{user_first_name}}! Welcome to " . $agent->agent_name . "!"
                    ]
                ],
                "persistent_menu"=> [
                    [
                        "locale" => "default",
                        "composer_input_disabled" => false,
                        "call_to_actions" => [
                            [
                                "type" => "postback",
                                "title" => "GO",
                                "payload" => "Start Browsing"
                            ],
                            [
                                "type" => "nested",
                                "title" => "Menu",
                                "call_to_actions" => [
                                    [
                                        "type" => "web_url",
                                        "title" => "Usha Bag/Profile",
                                        "url" => "https://usha.ulkabd.com/webview/cart",
                                        "webview_height_ratio" => "full",
                                        "messenger_extensions" => true,
                                        "webview_share_button" => "hide"
                                    ],
                                    // [
                                    //     "type" => "nested",
                                    //     "title" => "Human Assistance",
                                    //     "call_to_actions" => [
                                            [
                                                "type" => "postback",
                                                "title" => "Talk to Human",
                                                "payload" => "PAYLOAD_HA_ENABLE"
                                            ],
                                            [
                                                "type" => "postback",
                                                "title" => "Talk to Usha AI",
                                                "payload" => "PAYLOAD_HA_DISABLE"
                                            ]
                                    //     ]
                                    // ],
                                    // [
                                    //     "type" => "web_url",
                                    //     "title" => "Feedback/Complaint",
                                    //     "url" => "https://usha.ulkabd.com",
                                    //     "webview_height_ratio" => "full",
                                    //     "messenger_extensions" => true
                                    // ],
                                    // [
                                    //     "type" => "web_url",
                                    //     "title" => "FAQ",
                                    //     "url" => "https://usha.ulkabd.com",
                                    //     "webview_height_ratio" => "full",
                                    //     "messenger_extensions" => true
                                    // ]
                                ]
                            ],
                            [
                                "type" => "web_url",
                                "title" => "Usha | by Ulka Bangladesh",
                                "url" => "https://usha.ulkabd.com",
                                "webview_height_ratio" => "full"
                            ]
                        ]
                    ]
                ]
            ];

            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me/messenger_profile?access_token=' . $page_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->withData($data)
                ->post();

            return json_decode($response, true);
        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function unsetMessengerProfile($page_access_token) {
        try {
            $data = [
                "fields" => [
                    "whitelisted_domains",
                    "get_started",
                    "greeting",
                    "persistent_menu"
                ]
            ];

            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me/messenger_profile?access_token=' . $page_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->withData($data)
                ->delete();

            return json_decode($response, true);
        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function subscribeWebhookToPageEvents($page_access_token) {
        try {
            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me/subscribed_apps?access_token=' . $page_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->post();

            return json_decode($response, true);
        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function unSubscribeWebhookToPageEvents($page_access_token) {
        try {
            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me/subscribed_apps?access_token=' . $page_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->delete();

            return json_decode($response, true);
        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function getPageMetric($agent) {
        try {
            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/' . $agent->fb_page_id . '/insights/page_fans?access_token=' . $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->get();

            return json_decode($response, true);

        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function getUserProfile($user, $agent) {
        try {
            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/' . $user->agent_scoped_id .
                '?fields=first_name,last_name,profile_pic,locale,timezone,gender&access_token=' .
                $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->get();

            return json_decode($response, true);

        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function sendFacebookMessage($sender, $msgData, $agent, $messaging_type = 'RESPONSE', $message_tag = null) {
        try {
            if (!$messaging_type)
                throw new Exception("messaging_type param must be specified: RESPONSE, UPDATE, MESSAGE_TAG");

            $data = [
                'recipient' => [
                    'id' => $sender
                ],
                'message' => $msgData,
                'messaging_type' => $messaging_type
            ];

            if ($message_tag)
                $data['tag'] = $message_tag;

//            Log::info('Facebook message data: ');
//            Log::info(print_r($data, true));

            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me/messages?access_token=' . $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->withData($data)
                ->post();

            $data = json_decode($response, true);

            if (isset($data['body']['error'])) {
                throw new Exception($data['body']['error']);
            }
            else {
                return [
                    'error' => false,
                    'data' => $data
                ];
            }
        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function sendFacebookSenderAction($sender, $action, $agent) {
        try {
            $data = [
                'recipient' => [
                    'id' => $sender
                ],
                'sender_action' => $action
            ];

            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me/messages?access_token=' . $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->withData($data)
                ->post();

            $data = json_decode($response, true);

            if (isset($data['body']['error'])) {
                throw new Exception($data['body']['error']);
            }
            else {
                return [
                    'error' => false,
                    'data' => $data
                ];
            }
        }
        catch( Exception $e ) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function createBroadcastCustomLabel($label_name, $agent) {
        try {
            $data = [
                'name' => $label_name
            ];

            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me/custom_labels?access_token=' . $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->withData($data)
                ->post();

            $data = json_decode($response, true);

            if (isset($data['body']['error'])) {
                throw new Exception($data['body']['error']);
            }
            else {
                return [
                    'error' => false,
                    'data' => $data
                ];
            }
        }
        catch( Exception $e ) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function associateLabelToPSID($label_id, $psid, $agent) {
        try {
            $data = [
                'user' => $psid
            ];

            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api')
                . '/' . $label_id . '/label?access_token=' . $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->withData($data)
                ->post();

            $data = json_decode($response, true);

            if (isset($data['body']['error'])) {
                throw new Exception($data['body']['error']);
            }
            else {
                return [
                    'error' => false,
                    'data' => $data
                ];
            }
        }
        catch( Exception $e ) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function removeLabelFromPSID($label_id, $psid, $agent) {
        try {
            $data = [
                'user' => $psid
            ];

            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api')
                . '/' . $label_id . '/label?access_token=' . $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->withData($data)
                ->delete();

            $data = json_decode($response, true);

            if (isset($data['body']['error'])) {
                throw new Exception($data['body']['error']);
            }
            else {
                return [
                    'error' => false,
                    'data' => $data
                ];
            }
        }
        catch( Exception $e ) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function createMessageCreative(Array $messages, $agent) {
        try {
            $data = [
                'messages' => $messages
            ];

            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me/message_creatives?access_token=' . $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->withData($data)
                ->post();

            $data = json_decode($response, true);

            if (isset($data['body']['error'])) {
                throw new Exception($data['body']['error']);
            }
            else {
                return [
                    'error' => false,
                    'data' => $data
                ];
            }
        }
        catch( Exception $e ) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function sendBroadcast($agent, $message_creative_id, $notification_type = 'REGULAR', $custom_label_id = null, $tag = null) {
        try {
            $data = [
                'message_creative_id' => $message_creative_id,
                'notification_type' => $notification_type, //REGULAR | SILENT_PUSH | NO_PUSH
            ];

            if ($custom_label_id) $data['custom_label_id'] = $custom_label_id;
            if ($tag) $data['tag'] = $tag;


            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me/broadcast_messages?access_token=' . $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->withData($data)
                ->post();

            $data = json_decode($response, true);

            if (isset($data['body']['error'])) {
                throw new Exception($data['body']['error']);
            }
            else {
                return [
                    'error' => false,
                    'data' => $data
                ];
            }
        }
        catch( Exception $e ) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function startReachEstimation($agent, $custom_label_id = null) {
        try {
            $data = [];
            if ($custom_label_id) $data['custom_label_id'] = $custom_label_id;

            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api')
                . '/me/broadcast_reach_estimations?access_token=' . $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->withData($data)
                ->post();

            $data = json_decode($response, true);

            if (isset($data['body']['error'])) {
                throw new Exception($data['body']['error']);
            }
            else {
                return [
                    'error' => false,
                    'data' => $data
                ];
            }
        }
        catch( Exception $e ) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /*
     *
     */
    public static function getReachEstimation($reach_estimation_id, $agent) {
        try {
            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api')
                . '/' . $reach_estimation_id . '?access_token='  . $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->get();

            $data = json_decode($response, true);

            if (isset($data['body']['error'])) {
                throw new Exception($data['body']['error']);
            }
            else {
                return [
                    'error' => false,
                    'data' => $data
                ];
            }
        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function manageDomainsInWhitelisting($action, $page_access_token) {
        try {

            if (!isset($action) || (strcmp($action, 'add') !== 0 && strcmp($action, 'remove') !== 0)) {
                throw new Exception("var action must be 'add' or 'remove'");
            }

            $data = [
                "setting_type"=>"domain_whitelisting",
                "whitelisted_domains"=> [config('agent.base_url'), "http://m.me/UshaAI"],
                "domain_action_type"=> $action
            ];

            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me/thread_settings?access_token=' . $page_access_token)
                    ->withHeaders(
                        array( 'Content-Type: application/json' )
                    )
                    ->withData($data)
                    ->post();

            return json_decode($response, true);

        }catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function manageAgentDomainsInWhitelisting($action, $domains, $page_access_token) {
        try {

            if (!isset($action) || (strcmp($action, 'add') !== 0 && strcmp($action, 'remove') !== 0)) {
                throw new Exception("var action must be 'add' or 'remove'");
            }
            if (!$domains) {
                throw new Exception("Invalid domain list");
            }

            $data = [
                "setting_type"=>"domain_whitelisting",
                "whitelisted_domains"=> $domains,
                "domain_action_type"=> $action
            ];

            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me/thread_settings?access_token=' . $page_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->withData($data)
                ->post();

            return json_decode($response, true);

        }catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function postFeedAsPage($params, $agent) {
        try {
            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me/feed?access_token=' . $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->withData((array)$params)
                ->post();

            $data = json_decode($response, true);

            if (isset($data['body']['error'])) {
                throw new Exception($data['body']['error']);
            }
            elseif ($data == NULL) {
                throw new Exception($response);
            }
            else {
                return [
                    'error' => false,
                    'data' => $data
                ];
            }
        }
        catch( Exception $e ) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function postPhotoAsPage($params, $agent) {
        try {
            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/me/photos?access_token=' . $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->withData((array)$params)
                ->post();

            $data = json_decode($response, true);

            if (isset($data['body']['error'])) {
                throw new Exception($data['body']['error']);
            }
            elseif ($data == NULL) {
                throw new Exception($response);
            }
            else {
                return [
                    'error' => false,
                    'data' => $data
                ];
            }
        }
        catch( Exception $e ) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function deleteFacebookPost($postID, $agent) {
        try {
            $response = CurlAPI::to('https://graph.facebook.com/' . config('agent.facebook_protocols.v_api') . '/' . $postID . '?access_token=' . $agent->fb_access_token)
                ->withHeaders(
                    array( 'Content-Type: application/json' )
                )
                ->delete();

            $data = json_decode($response, true);

            if (isset($data['body']['error'])) {
                throw new Exception($data['body']['error']);
            }
            elseif ($data == NULL) {
                throw new Exception($response);
            }
            else {
                return [
                    'error' => false,
                    'data' => $data
                ];
            }
        }
        catch( Exception $e ) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}
