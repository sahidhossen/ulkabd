<?php

namespace App;

use App\Events\BroadcastTrainingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Agents extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'agent_code',
        'agent_name',
        'fb_page_id',
        'fb_page_name',
        'image_path',
        'apiai_dev_access_token',
        'apiai_client_access_token',
        'is_default_intents_fetched',
        'fb_access_token',
        'fb_verify_token',
        'fb_likes_count',
        'fb_opt_in_count',
        'is_fb_webhook',
        'page_subscription',
        'messenger_profile',
        'is_apiai_fb_integration',
        'training_status',
        'is_payment_due',
        'white_list_links',
        'fb_receiver_role'
    ];

    public function getAgentNameAttribute($name) {
        if ($this->fb_page_name != null) {
            return $this->fb_page_name;
        }
        else if ($name != null) {
            return $name;
        }
        else {
            return "Usha Agent";
        }
    }

    public function customers()
    {
        return $this->hasMany('App\EndUser');
    }

    public function products() {
        return $this->hasMany('App\Products', 'agent_id');
    }

    public function categories() {
        return $this->hasMany('App\Category');
    }

    /*
    * Get user roles based their permission assigned
    */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public static function setAgentInCache($key, $agent) {
        Redis::setEx($key, config('agent.ideal_redis_data_expiration_time'), json_encode($agent));
    }

    public static function getAgentFromCache($fb_page_id) {
        $agent = null;
        $key = "a:" . $fb_page_id;
        $serializedData = Redis::get($key);

        if ($serializedData != null) {
            Redis::expire($key, config('agent.ideal_redis_data_expiration_time'));

            $agent = json_decode($serializedData);
            return $agent;
        }
        else {
            $agent = Agents::where('fb_page_id', $fb_page_id )->first();
            Agents::setAgentInCache($key, $agent);

            return $agent;
        }
    }

    public static function delAgentFromCache($fb_page_id) {
        $key = "a:" . $fb_page_id;
        Redis::del($key);
    }

    public static function getCurrentAgent() {
        $agent = null;

        $user = Auth::user();
        if ($user) {
            $agent_code = Redis::get('agent_code_'.$user->id);
            if ($agent_code)
                $agent = Agents::where('agent_code', $agent_code )->first();
        }

        return $agent;
    }

    public static function setTrainingStatus($status, $agent = null) {
        if ($agent == null) {
            $agent = Agents::getCurrentAgent();
        }

        if ($agent) {
            $agent->training_status = $status;
            $agent->save();
        }
    }

    /**
     * @param null $agent
     * @return int
     */
    public static function getTrainingStatus($agent = null) {
        if ($agent == null) {
            $agent = Agents::getCurrentAgent();
        }

        if ($agent) {
//            Log::info('got current agent');
            return $agent->training_status;
        }
        else {
//            Log::info('Could not get current agent');
            return config('agent.training.done');
        }
    }

    /**
     *
     */
    public static function actionOnTrainingBroadCast() {
        $user = Auth::user();
        if (isset($user)) {
            event(
                new BroadcastTrainingStatus(
                    ['status' => Agents::getTrainingStatus()],
                    $user
                )
            );
        }
    }
}
