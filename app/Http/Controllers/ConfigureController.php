<?php

namespace App\Http\Controllers;

use App\Agents;
use Illuminate\Http\Request;
use League\Flysystem\Exception;

class ConfigureController extends Controller
{
    /**
     * ProductsController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function configure()
    {
        try {
            $agent = Agents::getCurrentAgent();
            $agent_status = $agent->page_subscription;
            return view('configure')->with('agent_status', $agent_status);
        } catch (Exception $e) {
            return view('configure')->with('agent_status', 0);
        }
    }
}
