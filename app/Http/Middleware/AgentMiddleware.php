<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
class AgentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $agent_code = $request->route()->agent_code;
        $user = Auth::User();
        //$request->session()->put('agent_code_'.$user->id, $agent_code);
        Redis::set( 'agent_code_'.$user->id, $agent_code);
        return $next($request);
    }

}
