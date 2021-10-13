<?php

namespace App;

use Illuminate\Support\Facades\Log;

class ApiaiIntent
{
    private $data;

    public function __construct($category, $data = null) {

        if ($data !== null) {
            $this->data = $data;
        }
        else if ($category !== null) {

            $action = mb_strtolower($category->name);
            $action = preg_replace('/\s+/', '-', $action);
//            $action = preg_replace('/[^A-Za-z0-9\-]/', '', $action);
            $action = preg_replace('/-+/', '-', $action);
            $action = 'a' . substr(uniqid(sha1($action)),0,20);
            
            $this->data = [
                'name' => $category->name,
                'auto' => true,
                'contexts' => [],
                'templates' => [],
                'userSays' => [],
                'responses' => [
                    [
                        'resetContexts' => false,
                        'action' => '.' . $action,
                        'affectedContexts' => [],
                        'parameters' => [],
                        'messages' => [],
                    ],
                ],
                'priority' => 500000,
                'webhookUsed' => false,
                'webhookForSlotFilling' => false,
                'fallbackIntent' => false,
                'events' => []
            ];
        }
    }

    public function setId($id) {
        if ($id !== null) {
            $this->data['id'] = $id;
        }
    }

    public function setName($name) {
        if ($name !== null) {
            $this->data['name'] = $name;
        }
    }

    public function addToUserSays($text) {
        if ($text !== null) {
            $userSays = [
                'isTemplate' => false,
                'count' => 0,
                'data' => [
                    [
                        'text' => $text
                    ]
                ]
            ];

            array_push($this->data['userSays'], $userSays);
        }
    }

    public function setUserSays($saysArray) {
        if ($saysArray !== null) {
            $this->data['userSays'] = $saysArray;
        }
    }

    public function setTemplates($templateArray) {
        if ($templateArray !== null) {
            $this->data['templates'] = $templateArray;
        }
    }

    public function addToTextResponse($speech) {
        if ($speech !== null) {
            array_push($this->data['responses'][0]['messages'], [
                'type' => 0,
                'speech' => $speech
            ]);
        }
    }

    public function setTextResponse($speechs) {
        // Log::info($speechs);
        $this->deleteTextResponcesIfAvailable();

        if ($speechs !== null) {
            $count = count($speechs);

            if ($count > 0) {
                for ($i = $count - 1; $i >= 0; --$i) { 
                    $speech = $speechs[$i];
    
                    array_unshift($this->data['responses'][0]['messages'], [
                        'type' => 0,
                        'speech' => $speech
                    ]);
                }
            }
        }
    }

    private function deleteTextResponcesIfAvailable() {
        if (isset($this->data['responses'][0]['messages'])) {
            $indices = array_keys(array_column($this->data['responses'][0]['messages'], 'type'), 0);

            if (count($indices) > 0)  {
                foreach($indices as $index) {
                    unset($this->data['responses'][0]['messages'][$index]);
                }
                $this->data['responses'][0]['messages'] = array_values($this->data['responses'][0]['messages']);
                return true;
            }
        }

        return false;
    }

    public function setFBResponse($fbResponseObject) {
        if ($fbResponseObject !== null) {
            array_push($this->data['responses'][0]['messages'], [
                'type' => 4,
                'payload' => [
                    'facebook' => $fbResponseObject
                ],
            ]);
        }
    }

    public function deleteFBResponseIfAvailable() {
        if (isset($this->data['responses'][0]['messages'])) {
            $index = array_search(4, array_column($this->data['responses'][0]['messages'], 'type'));

            if ($index === FALSE) {
                return false;
            }
            else {
                unset($this->data['responses'][0]['messages'][$index]);
                $this->data['responses'][0]['messages'] = array_values($this->data['responses'][0]['messages']);
                return true;
            }
        }

        return false;
    }

    public function setActionSlots($actionArray) {
        if ($actionArray !== null) {
            $this->data['responses'][0]['parameters'] = $actionArray;
        }
    }

    public function setWebhookStatus($bFlag) {
        $this->data['webhookUsed'] = $bFlag;
        $this->data['webhookForSlotFilling'] = $bFlag;
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->data;
    }

}
