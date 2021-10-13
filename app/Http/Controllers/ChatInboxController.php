<?php

namespace App\Http\Controllers;

use App\Agents;
use Illuminate\Http\Request;
use League\Flysystem\Exception;

class ChatInboxController extends Controller
{
    /**
     * ProductsController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function chat_inbox()
    {
        try {
            $agent = Agents::getCurrentAgent();
            $agent_status = $agent->page_subscription;
            return view('chat_inbox')->with('agent_status', $agent_status);
        } catch (Exception $e) {
            return view('chat_inbox')->with('agent_status', 0);
        }
    }
}
