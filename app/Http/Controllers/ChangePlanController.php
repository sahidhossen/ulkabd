<?php

namespace App\Http\Controllers;

use App\Agents;
use Illuminate\Http\Request;
use League\Flysystem\Exception;

class ChangePlanController extends Controller
{
    /**
     * ProductsController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function change_plan()
    {
        try {
            $agent = Agents::getCurrentAgent();
            $agent_status = $agent->page_subscription;
            return view('billing/change_plan')->with('agent_status', $agent_status);
        } catch (Exception $e) {
            return view('billing/change_plan')->with('agent_status', 0);
        }
    }
}
