<?php

namespace App\Http\Controllers;

use App\ApiaiWebhookResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiaiWebhookRequestController extends Controller
{


    public function apiai_webhook(Request $request) {
//        Log::info('Apiai webhook request:');
//        Log::info($request);

        $reqProcessor = new ApiaiWebhookResponse($request);
        $reqProcessor->processWebhookRequest();
        return $reqProcessor->response;
    }
}
