<?php

namespace App\Http\Composers;
use App\Agents;
use App\Orders;
use App\Products;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\View\View;
use League\Flysystem\Exception;

class NavigationComposer
{

    public function navBarCompose(View $view ){
        $user = Auth::User();
        $agents = $user->agents;

        $current_agent_code = Redis::get('agent_code_'.$user->id);
        if($current_agent_code == null ) {
            redirect(route('bots'));
        }
        $active_agent = Agents::where('agent_code', $current_agent_code)->first();

        $view->with( array('agents'=>$agents, 'active_agent'=>$active_agent,'app_id'=>config('agent.facebook_protocols.app_id')) );
    }

    public function sideBarCompose(View $view ){
        $user = Auth::user();
        $current_agent_code = Redis::get('agent_code_'.$user->id);
        
        if($current_agent_code == null) {
            redirect(route('bots'));
        }

        $active_agent = Agents::where('agent_code', $current_agent_code)->first();
        $productCount = Products::totalUsableProducts();
        $orderCount = Orders::active_orders_counter( $active_agent->id );
        $view->with( array('active_agent'=>$active_agent,'total_product'=>$productCount,"total_order"=>$orderCount) );
    }

    public function footerCompose(View $view ){
        $user = Auth::user();
        $current_agent_code = Redis::get('agent_code_'.$user->id);

        if($current_agent_code == null) {
            redirect(route('bots'));
        }
        $active_agent = Agents::where('agent_code', $current_agent_code)->first();
        $view->with( array('active_agent'=>$active_agent,'app_id'=>config('agent.facebook_protocols.app_id')) );
    }

}