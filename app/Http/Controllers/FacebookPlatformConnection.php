<?php

namespace App\Http\Controllers;

use App\Agents;
use App\FacebookAPI;
use App\Jobs\SetupMessengerInterface;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Facebook\Facebook;
use League\Flysystem\Exception;

class FacebookPlatformConnection extends Controller {

    use DispatchesJobs, Queueable;

    private $app_id;
    private $app_secret;
    private $permissions;

    public function __construct()
    {
        $this->middleware('auth');
        $this->app_id = config('agent.facebook_protocols.app_id');
        $this->app_secret = config('agent.facebook_protocols.app_secret');
        $this->permissions = config('agent.facebook_protocols.permissions');
        $this->version = config('agent.facebook_protocols.v_api');
    }

    public function index(Request $request) {
        $current_agent = Agents::getCurrentAgent();
        session_start();
        $fb_access_token = session('fb_access_token');

        $pages = null;
        if($current_agent->is_apiai_fb_integration == false && $fb_access_token) {
            $pages = $this->getAllPages($fb_access_token);
        }

        $data = [
            'appId' => $this->app_id,
            'fb_api_version' => $this->version,
            'permissions' => $this->permissions,

            'all_user_pages' => $pages,
            'agent' => $current_agent
        ];

        // if ($current_agent->is_apiai_fb_integration == false)
        //     $withData['permissions'] = $this->permissions;

        return view("facebook.connect")->with($data);
    }

    public function facebook_login_callback( Request $request ) {
        session_start() ;
        // session(['fb_access_token' => $request->fb_token]);

        $response = FacebookAPI::getLongLivedUserToken($this->app_id, $this->app_secret, $request->fb_token);

        // Log::info('Long lived token: ');
        // Log::info($response['data']);

        /**
        * (
        * 'access_token' => 'EAABt1IrCfgABAI9GnqAzr36LjwQmwkZBMLU9k5SnGiFOnw5OWuIWH1il2LZARHVSyuP3ODJjuXZBs8vz7D8UOT6ZBq9q0HT2dHghE4eRa69mBsmC8xoX5QJSEDCLEFyqFsELr6tMBDoToyDZAZCgMJEF88Iy1bgZAmgpS173MZBERQZDZD',
        * 'token_type' => 'bearer',
        * 'expires_in' => 5183532,
        * )
         */

        if ($response['error'] == false) {

            session(['fb_access_token' => $response['data']['access_token']]);

            return [
                'error' => false,
                'redirect_url' => '/connect_fb_page'
            ];
        }
        else {
            return [
                'error' => true,
                'redirect_url' => null
            ];
        }
    }

    private function getAllPages( $fb_access_token )
    {

        try {
            // Instantiates a new Facebook super-class object from SDK Facebook\Facebook
            $fb = new Facebook([
                'app_id'     => $this->app_id,
                'app_secret' => $this->app_secret,
                'default_access_token' => ($fb_access_token) ?
                    $fb_access_token : $this->app_id.'|'.$this->app_secret, // optional
            ]);
            $response = $fb->get('/me?fields=accounts,name,email');
            $graphObject = $response->getDecodedBody();

            $pages = isset($graphObject['accounts']) ? $graphObject['accounts'] : null;

            // Log::info('Pages with token: ');
            // Log::info($pages);

            return $pages;

        }
        catch (FacebookResponseException $e) {
            // Session::flash('error', 'Facebook SDK returned an error: ' . $e->getMessage());
            return null;
        }
        catch( Exception $e ) {
            return null;
        }
    }

    private function setupMessenger($agent, $page_access_token)
    {
        try {
            if ($agent->page_subscription == false) {
                $sRes = FacebookAPI::subscribeWebhookToPageEvents($page_access_token);

                Log::info('Fb subscription setup res: ');
                Log::info($sRes);

                if (isset($sRes['success']) && $sRes['success'] == true) {
                    $agent->page_subscription = true;
                    $agent->save();
                }
                else if (isset($sRes['error'])) {
                    throw new Exception($sRes['error']['message']);
                }
                else {
                    throw new Exception("Could not subscribe, please check your internet connection and try again!");
                }
            }

            if ($agent->messenger_profile == false) {
                usleep(100000);
                $dwlRes = FacebookAPI::manageDomainsInWhitelisting('add', $page_access_token);

                Log::info('Fb domain whitelisting res:');
                Log::info($dwlRes);

                usleep(100000);
                $pRes = FacebookAPI::setMessengerProfile($agent, $page_access_token);

                Log::info('Fb interface setup res: ');
                Log::info($pRes);

                if (isset($pRes['result']) && $pRes['result'] == 'success') {
                    $agent->messenger_profile = true;
                    $agent->save();
                }
                else if (isset($sRes['error'])) {
                    throw new Exception($sRes['error']['message']);
                }
                else {
                    throw new Exception("Could not setup messenger profile, please check your internet connection and try again!");
                }
            }
        }
        catch( Exception $e ) {
            throw $e;
        }
    }

    /*
     * Activate the facebook page
     * @parms
     */
    public function connect_facebook_page( Request $request ){
        try {
            $fb_access_token = $request->input('fb_access_token');
            $fb_page_name = $request->input('fb_page_name');
            $fb_page_id = $request->input('fb_page_id');

            if ($fb_access_token == null) throw new Exception("fb_access_token cannot be null!");
            if ($fb_page_name == null) throw new Exception("fb_page_name cannot be null!");
            if ($fb_page_id == null) throw new Exception("fb_page_id cannot be null!");

            $current_agent = Agents::getCurrentAgent();

            if ($current_agent == null) throw new Exception("Could not find agent");

            $this->setupMessenger($current_agent, $fb_access_token);

            $current_agent->is_apiai_fb_integration = true;
            $current_agent->page_subscription = true;
            $current_agent->is_fb_webhook = true;
            $current_agent->fb_page_id = $fb_page_id;
            $current_agent->fb_access_token = $fb_access_token;
            $current_agent->fb_page_name = $fb_page_name;
            $current_agent->save();

            return ['error'=>false,'message'=>"Page connected successfully!"];

        }catch(Exception $e ){
            Log::info('Facebook SDK returned an error: ' . $e->getMessage());
            return ['error'=>true,'message'=>$e->getMessage()];
        }
    }

    /*
     *
     */
    public function disconnect_facebook_page( Request $request ){
        try {
            $fb_access_token = $request->input('fb_access_token');

            if ($fb_access_token == null) throw new Exception("fb_access_token cannot be null!");

            $current_agent = Agents::getCurrentAgent();

            if ($current_agent == null) throw new Exception("Could not find agent");

            $sRes = FacebookAPI::unSubscribeWebhookToPageEvents($fb_access_token);

            Log::info($sRes);

            usleep(100000);
            $unsetProfileRes = FacebookAPI::unsetMessengerProfile($fb_access_token);
            Log::info($unsetProfileRes);
            usleep(100000);
            $removeWhitelistings = FacebookAPI::manageDomainsInWhitelisting('remove', $fb_access_token);
            Log::info($removeWhitelistings);

            if (
            (isset($sRes['success']) && $sRes['success'] == true)
            ||
            (isset($sRes['error']['code']))
            ) {

                Agents::delAgentFromCache($current_agent->fb_page_id);

                $current_agent->is_apiai_fb_integration = false;
                $current_agent->page_subscription = false;
                $current_agent->is_fb_webhook = false;
                $current_agent->messenger_profile = false;
                $current_agent->fb_page_id = NULL;
                $current_agent->fb_access_token = NULL;
                $current_agent->fb_page_name = NULL;
                $current_agent->save();

                return ['error'=>false, 'message'=>"Page disconnected successfully!"];
            }
            else throw new Exception("Could not disconnect " . $current_agent->fb_page_name . ", please try again!");

        }catch ( Exception $e ){
            return ['error'=>true, 'message'=>$e->getMessage() ];
        }
    }

}
