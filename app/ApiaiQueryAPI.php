<?php

namespace App;

class ApiaiQueryAPI
{
    public static function apiaiQuery($text, $sessionId, $message, $agent){
        $data = [
            'query' => $text,
            'sessionId' => $sessionId,
            'lang' => 'en',
            'originalRequest' => [
                'data' => $message,
                'source' => 'facebook'
            ]
        ];

        $response = CurlAPI::to('https://api.api.ai/v1/query?v=20150910')
            ->withHeaders(
                array(
                    'Authorization: Bearer '.$agent->apiai_client_access_token,
                    'Content-Type: application/json; charset=utf-8'
                )
            )
            ->withData($data)
            ->post();

        $jsonResponse = json_decode($response, true);

        return $jsonResponse;
    }
}
