<?php

namespace App;

use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class ApiaiEntityAPI
{

    public static function getEntityWithId($id, $agent) {
        try {
            if ($id == null) {
                throw new Exception('Must provide intent id');
            }

            $access = $agent->apiai_dev_access_token;

            $response = CurlAPI::to('https://api.api.ai/v1/entities/' . $id .'?v=20150910')
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

    public static function postEntity($data, Agents $agent) {
        try {
            $access = $agent->apiai_dev_access_token;

            $response = CurlAPI::to('https://api.api.ai/v1/entities?v=20150910')
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

    public static function putEntity(Category $category, $data, Agents $agent) {
        try {
            $access = $agent->apiai_dev_access_token;

            //https://api.api.ai/v1/entities/80f817e8-23fb-4e8e-ba62-eca1fcef7c3a?v=20150910
            $response = CurlAPI::to('https://api.api.ai/v1/entities/' . $category->apiai_entity_id . '?v=20150910')
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

    public static function deleteEntityWithId($id, Agents $agent) {
        try {

            if ($id == null) {
                throw new Exception('Must provide intent id');
            }

            $access = $agent->apiai_dev_access_token;

            $response = CurlAPI::to('https://api.api.ai/v1/entities/' . $id . '?v=20150910')
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

    public static function addEntriesToEntityWithId($id, $entries, $agent) {
        try {
            if ($id == null) {
                throw new Exception('Must provide intent id');
            }

            $access = $agent->apiai_dev_access_token;

            $response = CurlAPI::to('https://api.api.ai/v1/entities/' . $id . '/entries?v=20150910')
                ->withHeaders(
                    array(
                        'Authorization: Bearer '.$access,
                        'Content-Type: application/json; charset=utf-8'
                    )
                )
                ->withData($entries)
                ->post();

            $data = json_decode($response, true);

//            Log::info('Entries POST api response:');
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

    public static function updateEntriesToEntityWithId($id, $entries, $agent) {
        try {
            if ($id == null) {
                throw new Exception('Must provide intent id');
            }

            $access = $agent->apiai_dev_access_token;

            $response = CurlAPI::to('https://api.api.ai/v1/entities/' . $id . '/entries?v=20150910')
                ->withHeaders(
                    array(
                        'Authorization: Bearer '.$access,
                        'Content-Type: application/json; charset=utf-8'
                    )
                )
                ->withData($entries)
                ->put();

            $data = json_decode($response, true);

//            Log::info('Entries PUT api response:');
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

    public static function deleteEntriesFromEntityWithId($id, $refValues, $agent) {
        try {
            if ($id == null) {
                throw new Exception('Must provide intent id');
            }

            $access = $agent->apiai_dev_access_token;

            $response = CurlAPI::to('https://api.api.ai/v1/entities/' . $id . '/entries?v=20150910')
                ->withHeaders(
                    array(
                        'Authorization: Bearer '.$access,
                        'Content-Type: application/json; charset=utf-8'
                    )
                )
                ->withData($refValues)
                ->delete();

            $data = json_decode($response, true);

//            Log::info('Entries Delete api response:');
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
