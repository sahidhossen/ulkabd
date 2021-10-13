<?php

namespace App;

use App\Jobs\FacebookAPICall;
use App\Jobs\FacebookProfileFetch;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Exception;

class FacebookBot
{
    use DispatchesJobs, Queueable;

    protected $sessionId = null;
    protected $agent     = null;

    public function __construct()
    {

    }

    public function processMessage($msg, $agent) {
        try {
            $this->agent = $agent;

            $text = $this->getMessageText($msg);

            if ($text != null) {
                $sender = $msg['sender']['id'];
                $this->sessionId = $this->sessionIdForSender($sender, $agent);

                Log::info('Text: ' . $text);
                Log::info('Sender: ' . $sender);
                Log::info('Session id: ' . $this->sessionId);

                $apiaiRes = ApiaiQueryAPI::apiaiQuery($text, $this->sessionId, $msg, $agent);

                Log::info('Api ai res: ');
                Log::info($apiaiRes);

                if (
                    isset($apiaiRes['status']) && $apiaiRes['status']['code'] == 200 &&
                    isset($apiaiRes['result']) && isset($apiaiRes['result']['fulfillment'])
                ) {
                    $responseText = isset($apiaiRes['result']['fulfillment']['speech']) ?
                        $apiaiRes['result']['fulfillment']['speech'] : null;

                    $responseData = isset($apiaiRes['result']['fulfillment']['data']) ?
                        $apiaiRes['result']['fulfillment']['data'] : null;

                    $responseMessages = isset($apiaiRes['result']['fulfillment']['messages']) ?
                        $apiaiRes['result']['fulfillment']['messages'] : null;

                    if ($responseData != null && isset($responseData['facebook'])) {
                        Log::info('Going to send data response');
                        $facebookResponseData = $responseData['facebook'];

                        $this->sendDataResponse($sender, $facebookResponseData);
                    }
                    elseif ($responseMessages != null && count($responseMessages) > 0) {
                        Log::info('Going to send rich content response');
                        $this->sendRichContentResponse($sender, $responseMessages);
                    }
                    elseif ($responseText != null) {
                        Log::info('Going to send text response');
                        $this->sendTextResponse($sender, $responseText);
                    }
                }
                else {
                    Log::info('Error: ' . $apiaiRes['status']['errorDetails']);
                }
            }
        }
        catch( Exception $e ) {
            Log::info('FacebookBot Exception: ' . $e->getMessage());
        }
    }

    private function sessionIdForSender($sender, $agent) {
        $sessionId = Redis::get($sender);

        if ($sessionId == FALSE) {
            $end_user = EndUser::where(['agent_scoped_id' => $sender])->first();

            if ($end_user == null) {
                $end_user = new EndUser();

                $end_user->agent_id = $agent->id;
                $end_user->agent_scoped_id = $sender;
                $end_user->session_id = UUID::v4();
                $end_user->platform = 'facebook';
                $end_user->save();
            }

            if ($end_user->first_name == null) {
                $this->dispatch((new FacebookProfileFetch($end_user, $agent))
                    ->onQueue(config('queueNames.messenger_updater')));
            }
//            elseif ( Carbon::now()->diffInDays($end_user->updated_at) > 30 * 6 ) {
//
//            }

            Redis::setEx($sender, 180, $end_user->session_id);
            $sessionId = $end_user->session_id;
        }
        else {
            Redis::expire($sender, 180);
        }

        return $sessionId;
    }

    private function getMessageText($msg) {
        if (isset($msg['message'])) {
            if (isset($msg['message']['quick_reply']) && isset($msg['message']['quick_reply']['payload'])) {
                return $msg['message']['quick_reply']['payload'];
            }

            if (isset($msg['message']['text'])) {
                return $msg['message']['text'];
            }
        }

        if (isset($msg['postback']) && isset($msg['postback']['payload'])) {
            return $msg['postback']['payload'];
        }

        return null;
    }

    private function splitResponse($str) {
        if (strlen($str) <= config('agent.facebook_protocols.text_limit')) {
            return [$str];
        }

        return $this->chunkString($str, config('agent.facebook_protocols.text_limit'));
    }

    private function chunkString($s, $len) {

        $curr = $len;
        $prev = 0;

        $output = [];

        while ($s[$curr]) {
            if ($s[$curr++] == ' ') {
                array_push($output, substr($s, $prev, $curr));
                $prev = $curr;
                $curr += $len;
            }
            else {
                $currReverse = $curr;
                do {
                    if (substr($s, $currReverse - 1, $currReverse) == ' ') {
                        array_push($output, substr($s, $prev, $currReverse));
                        $prev = $currReverse;
                        $curr = $currReverse + $len;
                        break;
                    }
                    $currReverse--;
                } while ($currReverse > $prev);
            }
        }
        array_push($output, substr($s, $prev, null));

        return $output;

    }

    private function sendDataResponse($sender, $responseData) {
        if (isset($responseData[0]) == false) {
            $responseData = [$responseData];
        }

        $this->dispatch((new FacebookAPICall($sender, $responseData, $this->agent, 'data_responses'))
            ->onQueue(config('queueNames.messenger_updater')));
    }

    private function sendRichContentResponse($sender, $messages) {
        $fbMessages = [];
        $messageIndex = 0;

        for ($messageIndex; $messageIndex < count($messages); ++$messageIndex) {
            $message = $messages[$messageIndex];

            switch($message['type']) {
                case 0: {
                    if (isset($message['speech'])) {
                        $splittedText = $this->splitResponse($message['speech']);

                        foreach($splittedText as $text) {
                            array_push($fbMessages, ['text' => $text]);
                        }
                    }
                }
                break;

                case 1: {
                    $carousel = [$message];

                    for ($messageIndex++; $messageIndex < count($messages); $messageIndex++) {
                        if ($messages[$messageIndex]['type'] == 1) {
                            array_push($carousel, $messages[$messageIndex]);
                        } else {
                            $messageIndex--;
                            break;
                        }
                    }

                    $facebookMessage = [];

                    foreach($carousel as $c) {
                        $card = [];

                        $card['title'] = $c['title'];
                        $card['image_url'] = $c['image_url'];
                        if (isset($c['subtitle'])) {
                            $card['subtitle'] = $c['subtitle'];
                        }

                        if (isset($c['buttons']) && count($c['buttons']) > 0) {
                            $buttons = [];

                            for ($buttonIndex = 0; $buttonIndex < count($c['buttons']); $buttonIndex++) {
                                $button = $c['buttons'][$buttonIndex];

                                if (isset($button['text'])) {
                                    $postback = isset($button['postback']) ? $button['postback'] : $button['text'];

                                    $buttonDescription = [
                                        'title' => $button['text']
                                    ];

                                    if (starts_with($postback, 'http')) {
                                        $buttonDescription['type'] = 'web_url';
                                        $buttonDescription['url'] = $postback;
                                    } else {
                                        $buttonDescription['type'] = 'postback';
                                        $buttonDescription['url'] = $postback;
                                    }

                                    array_push($buttons, $buttonDescription);
                                }
                            }

                            if (count($buttons) > 0) {
                                $card['buttons'] = $buttons;
                            }
                        }

                        if (! isset($facebookMessage['attachment'])) {
                            $facebookMessage['attachment'] = [
                                'type' => 'template'
                            ];
                        }

                        if (! isset($facebookMessage['attachment']['payload']) ) {
                            $facebookMessage['attachment']['payload'] = [
                                'template_type' => 'generic',
                                'elements' => []
                            ];
                        }

                        array_push($facebookMessage['attachment']['payload']['elements'], $card);
                    }

                    array_push($fbMessages, $facebookMessage);
                }
                break;

                case 2: {
                    if (isset($message['replies']) && count($message['replies']) > 0) {
                        $facebookMessage = [];

                        $facebookMessage['text'] = isset($message['title']) ? $message['title'] : 'Choose an item:';
                        $facebookMessage['quick_replies'] = [];

                        foreach($message['replies'] as $reply) {
                            array_push($facebookMessage['quick_replies'], [
                                'content_type' => 'text',
                                'title' => $reply,
                                'payload' => $reply
                            ]);
                        }

                        array_push($fbMessages, $facebookMessage);
                    }
                }
                break;

                case 3: {

                    if (isset($message['imageUrl'])) {
                        $facebookMessage = [];

                        $facebookMessage['attachment'] = [
                            'type' => 'image'
                        ];
                        $facebookMessage['attachment']['payload'] = [
                            'url' => $message['imageUrl']
                        ];

                        array_push($fbMessages, $facebookMessage);
                    }
                }
                break;

                case 4: {

                    if (isset($message['payload']) && isset($message['payload']['facebook'])) {
                        array_push($fbMessages, $message['payload']['facebook']);
                    }

                }
                break;

                default: break;
            }
        }

        $this->dispatch((new FacebookAPICall($sender, $fbMessages, $this->agent, 'rich_responses'))
            ->onQueue(config('queueNames.messenger_updater')));

    }

    private function sendTextResponse($sender, $responseText) {
        $splitedText = $this->splitResponse($responseText);

        $this->dispatch((new FacebookAPICall($sender, $splitedText, $this->agent, 'text_responses'))
            ->onQueue(config('queueNames.messenger_updater')));
    }

}
