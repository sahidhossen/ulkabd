<?php

namespace App;

use App\Events\BroadcastNewOrder;
use App\Jobs\FacebookProfileFetch;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Exception;

class ApiaiWebhookResponse
{

    use DispatchesJobs, Queueable;

    private $request;
    private $timeOut;
    public $response;

    private $senderId;
    private $senderName;
    private $source;

    public function __construct($request)
    {
        try {
            $this->request = $request;
            $this->timeOut = config('agent.ideal_redis_data_expiration_time');

            $defaultSpeech = $this->request->input('result.fulfillment.speech');

            $this->response = [
                'speech' => '',
                'displayText' => '',
                'source' => 'https://usha.ulkabd.com',
                'data' => [
                    'facebook' => [
                        // Response for 'Cancel' text
                        'text' => $defaultSpeech ?
                            $defaultSpeech : 'Ok cancelled, but I can help you find what you need, just ask.'
                    ]
                ]
            ];

            $this->senderId = $this->request->input('originalRequest.data.sender.id');
            $this->senderName = ($this->request->input('originalRequest.data.sender.name'))
                ? $this->request->input('originalRequest.data.sender.name') : null;
            $this->source = ($this->request->input('originalRequest.source'))
                ? $this->request->input('originalRequest.source') : 'facebook';
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function processWebhookRequest()
    {
        try {
            $recipientId = $this->request->input('originalRequest.data.recipient.id');

            // Get agent from cache
            $agent = Agents::getAgentFromCache($recipientId);
            // Log::info(print_r($agent, true));

            //Fetch user, keep in redis for 3mins
            $session = $this->sessionForSender($this->senderId, $agent);

            if (isset($agent) && isset($session)) {
                // Intent and category name are same
                $intent = $this->request->input('result.metadata.intentName');

                if ($intent === 'Greeting') {
                    $this->response['data']['facebook'] = FacebookResponseTypes::genericCardsWith([
                        [
                            "title" => $agent->agent_name,
                            "image_url" => isset($agent->image_path) ?
                                config('agent.base_url') . '/uploads/' . $agent->image_path :
                                'https://usha.ulkabd.com/images/usha.png',
                            "subtitle" => 'Welcome ' . $session->first_name .
                                '! Start Browsing and I will find all your needs.',
                            "buttons" => [
                                [
                                    "button_title" => 'Start Browsing',
                                    "button_payload" => 'Start Browsing'
                                ]
                            ]
                        ]
                    ]);
                } else {
                    $parameters = $this->request->input('result.parameters');
                    $query = $this->request->input('result.resolvedQuery');
                    $category = Category::where([
                        'agent_id' => $agent->id,
                        'apiai_intent_name' => $intent
                    ])
                        ->first();

                    if ($category && $category->rss_feed == true) {
                        if (!$category->external_link) {
                            $this->response['data']['facebook']['text'] =
                                'Unfortunately I could not find anything about ' . strtoupper($query) . ' at this moment!';
                        } else {
                            $this->response['data']['facebook'] = FacebookResponseTypes::rssResponseWith(
                                RSSFeedProcessor::feedsFromRSS($category->external_link),
                                $query
                            );
                        }
                    }
                    /*if (strpos($query, config('agent.facebook_protocols.payload_product_detail'), 0) !== false) {
                        $data = explode('-', $query);
                        if ($data && count($data) > 0) {
                            $product_id = $data[1];

                            $product = Products::find($product_id);
                            if ($product) {
                                //if detail > 640 chunk string into 640 chunks
                                //if image add image data
                                //if array array of string chunks
                                //last chunk is with Add to cart with price button

                                $this->response['data']['facebook'] = [];

                                $img_url = null;
                                if ($product->is_image)
                                    $img_url = config('agent.base_url') . '/uploads/' . $product->is_image;
                                else if ($product->image_link)
                                    $img_url = $product->image_link;

                                if($img_url) {
                                    $this->response['data']['facebook'][] = [
                                        "attachment" => [
                                            "type" => "image",
                                            "payload" => [
                                                "url" => $img_url,
                                                "is_reusable" => false
                                            ]
                                        ]
                                    ];
                                }

                                $detail = $product->name . PHP_EOL . $product->detail;
                                $detailArray = str_split($detail, config('agent.facebook_protocols.text_limit'));

                                $count = count($detailArray);
                                for ($i = 0; $i < $count; ++$i) {
                                    $text = $detailArray[$i];

                                    if ($i < $count - 1) {
                                        $this->response['data']['facebook'][] = [
                                            'text' => $text
                                        ];
                                    }
                                    else {
                                        $this->response['data']['facebook'][] = [
                                            "attachment" => [
                                                "type" => "template",
                                                "payload" => [
                                                    "template_type" => "button",
                                                    "text" => $text,
                                                    "buttons" => [
                                                        [
                                                            "type" => "postback",
                                                            "title" => "\xF0\x9F\x9B\x92" . ' (' . $product->offer_price . ' TK)',
                                                            "payload" => $category->apiai_entity_name . '-' . $product->code
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ];
                                    }
                                }
                            }
                            else {
                                $this->response['data']['facebook']['text'] = 'Sorry! I could not find a detail for for this product.';
                            }
                        }
                    }*/ else if (
                        isset($parameters) &&
                        count($parameters) > 0 &&
                        isset($category)
                    ) {

                        if (isset($parameters[$category->apiai_entity_name]) === FALSE) {
                            $pCount = $category
                                ->products()
                                ->where('flag', '!=', config('agent.flag.deleted'))
                                ->where('stock', '!=', 0)
                                ->count();

                            if ($pCount == 0) {
                                $this->response['data']['facebook']['text'] =
                                    'Unfortunately there are no products in this category, but you can always look further.';
                            } else if (stripos($query, 'next') !== FALSE) {
                                $session->offset += 1;

                                $products = $category
                                    ->products()
                                    ->where('flag', '!=', config('agent.flag.deleted'))
                                    ->where('stock', '!=', 0)
                                    ->orderBy('priority')
                                    ->limit($session->limit)
                                    ->offset($session->offset * $session->limit)
                                    ->get();

                                // Create product cards
                                if (isset($products) && count($products) > 0) {
                                    $this->response['data']['facebook'] = FacebookResponseTypes::genericCardsWith(
                                        ProductsToFBTemplatesMapper::cardsDataArray(
                                            $products,
                                            $category->apiai_entity_name,
                                            '#price'
                                        )
                                    );
                                }

                                // Create quick action buttons - Previous | Next
                                $buttons = [
                                    [
                                        'title' => 'Previous',
                                        'payload' => 'Previous'
                                    ],
                                    [
                                        'title' => 'Cancel',
                                        'payload' => 'Cancel'
                                    ]
                                ];
                                if ($pCount > (($session->offset + 1) * $session->limit)) {
                                    $buttons[] = [
                                        'title' => 'Next',
                                        'payload' => 'Next'
                                    ];
                                }
                                $quickBtns = FacebookResponseTypes::quickRepliesWith(
                                    null,
                                    $buttons
                                );
                                $this->response['data']['facebook']['quick_replies'] = $quickBtns['quick_replies'];

                                $this->updateSession($this->senderId, $session);
                            } else if (stripos($query, 'previous') !== FALSE) {
                                $session->offset -= 1;
                                $products = $category
                                    ->products()
                                    ->where('flag', '!=', config('agent.flag.deleted'))
                                    ->where('stock', '!=', 0)
                                    ->orderBy('priority')
                                    ->limit($session->limit)
                                    ->offset($session->offset * $session->limit)
                                    ->get();

                                // Create product cards
                                if (isset($products) && count($products) > 0) {
                                    $this->response['data']['facebook'] = FacebookResponseTypes::genericCardsWith(
                                        ProductsToFBTemplatesMapper::cardsDataArray(
                                            $products,
                                            $category->apiai_entity_name,
                                            '#price'
                                        )
                                    );
                                }

                                // Create quick action buttons - Previous | Next
                                $buttons = [
                                    [
                                        'title' => 'Cancel',
                                        'payload' => 'Cancel'
                                    ],
                                    [
                                        'title' => 'Next',
                                        'payload' => 'Next'
                                    ]
                                ];
                                if ($session->offset > 0) {
                                    array_unshift($buttons, [
                                        'title' => 'Previous',
                                        'payload' => 'Previous'
                                    ]);
                                }
                                $quickBtns = FacebookResponseTypes::quickRepliesWith(
                                    null,
                                    $buttons
                                );
                                $this->response['data']['facebook']['quick_replies'] = $quickBtns['quick_replies'];

                                $this->updateSession($this->senderId, $session);
                            }
                            // Product borwsing starts here
                            else {
                                $session->totalProducts = $pCount;
                                $session->offset = 0;
                                $products = $category
                                    ->products()
                                    ->where('flag', '!=', config('agent.flag.deleted'))
                                    ->where('stock', '!=', 0)
                                    ->orderBy('priority', 'desc')
                                    ->limit($session->limit)
                                    ->offset($session->offset)
                                    ->get();

                                $response = [];

                                $textResponses = json_decode($category->text_response);
                                if (isset($textResponses) && count($textResponses) > 0) {
                                    foreach ($textResponses as $textResponse) {
                                        array_push($response, ['text' => $textResponse]);
                                    }
                                }

                                $pResponse = [];

                                // Create product cards
                                if (isset($products) && count($products) > 0) {
                                    $pResponse = FacebookResponseTypes::genericCardsWith(
                                        ProductsToFBTemplatesMapper::cardsDataArray(
                                            $products,
                                            $category->apiai_entity_name,
                                            '#price'
                                        )
                                    );
                                }

                                // Create quick action buttons - Previous | Next
                                $buttons = [
                                    [
                                        'title' => 'Cancel',
                                        'payload' => 'Cancel'
                                    ]
                                ];
                                if ($pCount > $session->limit) {
                                    $buttons[] = [
                                        'title' => 'Next',
                                        'payload' => 'Next'
                                    ];
                                }
                                $quickBtns = FacebookResponseTypes::quickRepliesWith(
                                    null,
                                    $buttons
                                );
                                $pResponse['quick_replies'] = $quickBtns['quick_replies'];

                                if (count($response) > 0) {
                                    array_push($response, $pResponse);
                                    $this->response['data']['facebook'] = $response;
                                } else $this->response['data']['facebook'] = $pResponse;

                                $this->updateSession($this->senderId, $session);
                            }
                        } else {
                            // Fill Slots
                            if ($session->actionSlots->index === 0) {
                                $productId = $parameters[$category->apiai_entity_name];
                                $session->actionSlots->prepareSlots($category, $productId);
                            } else if (isset($parameters[$session->actionSlots->slots[$session->actionSlots->index]['key']])) {
                                // if ($session->actionSlots->slots[$session->actionSlots->index]['key'] === 'address') {
                                //     $session->actionSlots->slots[$session->actionSlots->index]['value'] = $query;
                                // } else {
                                $session->actionSlots->slots[$session->actionSlots->index]['value'] =
                                    $parameters[$session->actionSlots->slots[$session->actionSlots->index]['key']];
                                // }
                            } else {
                                $this->response['data']['facebook']['text'] =
                                    'Wrong input. ' . $this->response['data']['facebook']['text'];
                                return;
                            }

                            $session->actionSlots->index += 1;

                            if ($session->actionSlots->index < $session->actionSlots->count) {
                                $params = $session->actionSlots->slots[$session->actionSlots->index]['params'];
                                if (isset($params) && count($params) > 0) {
                                    $attButtons = FacebookResponseTypes::quickRepliesWith(
                                        null,
                                        $params
                                    );
                                    $this->response['data']['facebook']['quick_replies'] = $attButtons['quick_replies'];
                                }

                                $this->updateSession($this->senderId, $session);
                            } else {
                                // Order confirmed
                                $productId = $session->actionSlots->slots[0]['value'];
                                $product = Products::find($productId);

                                if (isset($product) && $product->stock != 0) {
                                    $attributes = [];

                                    for ($i = 1; $i < count($session->actionSlots->slots); ++$i) {
                                        $slot = $session->actionSlots->slots[$i];
                                        $attributes[] = [
                                            'name' => $slot['name'],
                                            'value' => $slot['value']
                                        ];
                                    }

                                    $cart = CartRedisCache::getCart($this->senderId);
                                    $cart->insertEntity($product->id, $attributes, 1);
                                    $this->response['data']['facebook'] = StandardFacebookResponses::addToCartConfirmationCardWith($product, $attributes);

                                    // Log::info("In redis cart: ");
                                    // Log::info(print_r($cart->getData(), true));

                                    // Log::info("Created session:");
                                    // Log::info(print_r($session, true));
                                } else {
                                    $this->response['data']['facebook']['text'] = 'Could not add to cart (Usha Bag). This product is out of stock or is unavailable!';
                                }

                                $this->deleteSession($this->senderId);
                            }
                        }
                    } else {
                        // Cancel
                        $this->deleteSession($this->senderId);
                    }
                }
            } else {
                $this->deleteSession($this->senderId);
                throw new Exception('Could not find agent or user session');
            }

            // Log::info(print_r($this->response['data']['facebook'], true));
        } catch (Exception $e) {
            Log::info($e->getMessage());

            $this->deleteSession($this->senderId);
            $this->response['data']['facebook']['text'] = 'Something wrong happened & I just lost myself! Please try that last thing again!';
        }
    }

    private function deleteSession($senderId)
    {
        Redis::del($senderId);
    }

    private function updateSession($senderId, $session)
    {
        Redis::setEx($senderId, $this->timeOut, serialize($session));
    }

    private function sessionForSender($senderId, $agent)
    {
        try {
            $sessionData = Redis::get($senderId);

            if ($sessionData == FALSE) {
                $session = new class{};
                $end_user = EndUser::where(['agent_scoped_id' => $senderId])->first();

                if ($end_user == null) {
                    $end_user = new EndUser();

                    $end_user->agent_id = $agent->id;
                    $end_user->agent_scoped_id = $senderId;
                    $end_user->session_id = UUID::v4();
                    $end_user->platform = $this->source;

                    // If web user, request contains 'sender name'
                    if ($this->senderName !== null && $this->source === 'web') {
                        $names = explode(' ', $this->senderName);
                        $end_user->first_name = $names[0];
                        if (count($names) > 1)
                            // Maximum two words as last name
                            $end_user->last_name = join(' ', array_slice($names, 1, 2));
                    }

                    $end_user->save();

                    $agent->fb_opt_in_count += 1;
                    $agent->save();
                }

                if ($end_user->first_name == null && $this->source === 'facebook') {
                    $this->getSenderFBProfile($end_user, $agent);
                }

                $session = $end_user;
                $session->category = null;
                $session->actionSlots = new ApiaiActionSlots();
                $session->totalProducts = 0;
                $session->offset = 0;
                $session->limit = config('agent.facebook_protocols.max_cards');

                Redis::setEx($senderId, $this->timeOut, serialize($session));

                return $session;
            } else {
                Redis::expire($senderId, $this->timeOut);
                $session = unserialize($sessionData);

                return $session;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getSenderFBProfile($user, $agent)
    {
        try {
            if ($agent->fb_access_token != null && $user->agent_scoped_id != null) {
                $response = FacebookAPI::getUserProfile($user, $agent);

                Log::info('Fb User profile: ');
                Log::info($response);

                $user->first_name = isset($response['first_name']) ? $response['first_name'] : null;
                $user->last_name = isset($response['last_name']) ? $response['last_name'] : null;
                $user->profile_pic = isset($response['profile_pic']) ? $response['profile_pic'] : null;
                $user->local = isset($response['locale']) ? $response['locale'] : null;
                $user->gender = isset($response['gender']) ? $response['gender'] : null;

                $user->save();

                return true;
            }

            return false;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
