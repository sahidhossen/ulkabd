<?php

namespace App;

use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class ApiaiIntentAPI
{
    public static function getIntents($agent) {
        try {
            $access = $agent->apiai_dev_access_token;

            $response = CurlAPI::to("https://api.api.ai/v1/intents?v=20150910")
                ->withHeaders(array(
                    "accept: application/json",
                    'Authorization: Bearer '.$access,
                    "content-type: application/x-www-form-urlencoded"
                ))
                ->get();

            $data = json_decode($response, true);

            if (isset($data['status']['errorType']) && $data['status']['errorType'] !== 'success') {
                throw new Exception($data['status']['errorDetails']);
            }
            else {
                return $data;
            }
        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function getIntentWithId($id, $agent) {
        try {
            if ($id == null) {
                throw new Exception('Must provide intent id');
            }

            $access = $agent->apiai_dev_access_token;

            $response = CurlAPI::to('https://api.api.ai/v1/intents/' . $id .'?v=20150910')
                ->withHeaders(array(
                    "accept: application/json",
                    'Authorization: Bearer '.$access,
                    "content-type: application/x-www-form-urlencoded"
                ))
                ->get();

            $data = json_decode($response, true);

            if (isset($data['status']['errorType']) && $data['status']['errorType'] !== 'success') {
                throw new Exception($data['status']['errorDetails']);
            }
            else {
                return $data;
            }
        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function postIntent($data, Agents $agent) {
        try {
            $access = $agent->apiai_dev_access_token;

            $response = CurlAPI::to('https://api.api.ai/v1/intents?v=20150910')
                ->withHeaders(
                    array(
                        'Authorization: Bearer '.$access,
                        'Content-Type: application/json; charset=utf-8'
                    )
                )
                ->withData($data)
                ->post();

            $responseData = json_decode($response, true);

            if (isset($responseData['status']['errorType']) && $responseData['status']['errorType'] !== 'success') {
                throw new Exception($responseData['status']['errorDetails']);
            }
            else {
                return $responseData;
            }
        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function putIntent(Category $category, $data, Agents $agent) {
        try {
            $access = $agent->apiai_dev_access_token;

            $response = CurlAPI::to('https://api.api.ai/v1/intents/' . $category->apiai_intent_id . '?v=20150910')
                ->withHeaders(
                    array(
                        'Authorization: Bearer '.$access,
                        'Content-Type: application/json; charset=utf-8'
                    )
                )
                ->withData($data)
                ->put();


            $responseData = json_decode($response, true);

            if (isset($responseData['status']['errorType']) && $responseData['status']['errorType'] !== 'success') {
                throw new Exception($responseData['status']['errorDetails']);
            }
            else {
                return $responseData;
            }
        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function deleteIntentWithId($id, Agents $agent) {
        try {

            if ($id == null) {
                throw new Exception('Must provide intent id');
            }

            $access = $agent->apiai_dev_access_token;

//            Log::info("https://api.api.ai/v1/intent/" . $id ."?v=20150910");

            $response = CurlAPI::to('https://api.api.ai/v1/intents/' . $id . '?v=20150910')
                ->withHeaders(
                    array(
                        'Authorization: Bearer '.$access,
                        'Content-Type: application/json; charset=utf-8'
                    )
                )
                ->delete();

            $data = json_decode($response, true);

//            Log::info('Delete api response:');
//            Log::info($data);

            if (isset($data['status']['errorType']) && $data['status']['errorType'] !== 'success') {
                throw new Exception($data['status']['errorDetails']);
            }
            else {
                return $data;
            }
        }
        catch( Exception $e ){
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}
