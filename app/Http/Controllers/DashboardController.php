<?php

namespace App\Http\Controllers;
use App\Agents;
use App\FacebookAPI;
use App\Products;
use Facebook\Facebook;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use App\Orders;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use League\Flysystem\Exception;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
     * Bots page
     */
    public function home( )
    {
        return view('home');
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

     public function orders_list(){
       $user = Auth::user();
       $agent_code = Redis::get('agent_code_'.$user->id);
       $current_agent = Agents::where('agent_code', $agent_code )->first();
       $all_orders = Orders::where('agent_id', $current_agent->id)->get();
       return $all_orders;
     }

    public function index() {
        return view('dashboard');
    }

    public function get_dashboard_initial_information(){
         try{
             $current_agent = Agents::getCurrentAgent();
             $orderCounter = Orders::active_orders_counter( $current_agent->id );
             $products = Products::totalUsableProducts();
             return ['error'=>false, 'current_agent'=>$current_agent, 'numberOfOrders'=>$orderCounter, 'products'=>$products];
         }catch (Exception $e ){
             return ['error'=>true,'message'=>$e->getMessage()];
         }
    }

    public function demo_page(){
        $data = ['http://emiliosubira.com/'];
        $current_agent = Agents::getCurrentAgent();
        $response = FacebookAPI::checkDomainWhiteListing($data, $current_agent->fb_access_token);
        return $response;
    }


    /*
     * Manage Home page
     */
    public function feedback(){
        return view('feedback.index');
    }

    /*
     * Faq page
     */
    public function faq(){
        return view('manage.faq');
    }

    /*
     * Aritificial Intelligent
     */
    public function artificialIntelligent(){
        $current_agent = Agents::getCurrentAgent();
        return view('AI.ai')->with(['agent'=>$current_agent]);
    }




}
