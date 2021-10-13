<?php

namespace App\Http\Controllers;

use App\Agents;
use App\Events\BroadcastTrainingStatus;
use App\Jobs\APIAIAgentUpdater;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use League\Flysystem\Exception;
use phpseclib\System\SSH\Agent;

class TrainAgentController extends Controller
{
    use DispatchesJobs, Queueable;

    /*
     * Start training
     * Ajax Request
     * @return array()
     * @Request null
     */
    public function train(){
        /*
         * dispatch the job from here
         */
        try {

            $user = Auth::user();
            $agent_code = Redis::get('agent_code_' . $user->id);
            $agent = Agents::where('agent_code', $agent_code)->first();

            $this->dispatch(
                (new APIAIAgentUpdater($agent, $user))
                    ->onQueue(
                        config('queueNames.agent_update')
                    )
            );

            return array(
                'error'=>false,
                'message'=>"Bot Training Started"
            );
        }
        catch( Exception $e ){
            return array(
                'error'=>true,
                'message'=> $e->getMessage()
            );
        }
    }

    public function TestBroadcast(Request $request ){
        try {
            $status = Agents::getTrainingStatus();
            $result = ['status'=>$status];
            event( new BroadcastTrainingStatus( $result, Auth::user()) );
            return ['error' => false];
        }catch( Exception $ex ){
            return ['error' => true];
        }
    }

    /*
     * Check training status
     */
    public function checkTrainingStatus(){
        try {
            $status = Agents::getTrainingStatus();
            $result = [
                'error'=>false,
                'status'=>$status
            ];
            return $result;
        }
        catch(Exception $ex ) {
            $result = ['error'=>false, 'message'=>$ex->getMessage()];
            return $result;
        }
    }
}
