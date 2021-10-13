<?php

namespace App\Http\Controllers;

use App\Agents;
use App\Broadcast;
use App\Jobs\ProcessBroadcast;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class SchedulesController extends Controller
{
    use DispatchesJobs;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('schedules', array('todaySchedule'=>'has schedule'));
    }

    public function create_schedule(Request $request ){
        try{

//            Log::info("Request: ");
//            Log::info(print_r($request->all(), true));

            $agent = Agents::getCurrentAgent();
            if( $agent == null )
                throw new Exception("Authentication error!");
            elseif(!$agent->page_subscription)
                throw new Exception("Please connect your facebook page before broadcasting messages.");

            $message = $request->input('message');

            if( $message =='' || $message == null )
                throw new Exception("Message parameter empty!");

            $broadcast = $this->create( $request );

            if( $broadcast == null ) {
                throw new Exception("Broadcast creation error!");
//                Log::info("Broadcast error-1: ", $broadcast);
            }

            $creative = json_decode($broadcast->creative, true);
            $product_ids = isset($creative['products']) ? $creative['products'] : null;

            dispatch(
                (new ProcessBroadcast(
                    $message,
                    $agent,
                    $broadcast,
                    $product_ids
                ))
                    ->onQueue(
                        config('queueNames.messenger_updater')
                    )
            );
            $broadcast->creative = json_decode( $broadcast->creative );
            return [
                'error'=>false,
                'message'=>"Your message is being broadcasted successfully!",
                'broadcast'=> $broadcast
            ];

        }catch(Exception $e){
            return ['error'=>true, 'message'=>$e->getMessage() ];
        }
    }

    /*
     * Store broadcast message
     */
    private function create( $request ){
        try {
            $agent = Agents::getCurrentAgent();

            if( $request->input('id') != '' || $request->input('id') != null )
                return Broadcast::find($request->input('id'));

            $broadcast = new Broadcast();
            $broadcast->agent_id = $agent->id;
            $broadcast->creative = json_encode(['text' => $request->input('message')]);
            if($broadcast->save()){
                return $broadcast;
            }
            return null;
        }catch(Exception $e){
            throw $e;
        }


    }


    /*
     * get all broadcast lists
     */
    public function broadcast_list_by_agent(){
        try{
            $agent = Agents::getCurrentAgent();
            if( $agent == null )
                throw new Exception("Authentication error!");

            $broadcast_by_agent = Broadcast::where(['agent_id'=>$agent->id])->get();
            if( count( $broadcast_by_agent )>0 ){
                foreach( $broadcast_by_agent as $key=>$broadcast ){
                    $broadcast_by_agent[$key]->creative = json_decode( $broadcast->creative );
                    $broadcast_by_agent[$key]->stat = ($broadcast->stat != null || $broadcast->stat != '' ) ? json_decode( $broadcast->stat) : null;
                }
            }
            return ['error'=>false, 'broadcasts'=>$broadcast_by_agent];
        }catch (Exception $e){
            return['error'=>true, 'message'=>$e->getMessage()];
        }
    }

}
