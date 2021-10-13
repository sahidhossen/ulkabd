<?php

namespace App\Http\Controllers;

use App\Agents;
use App\FacebookAPI;
use App\Jobs\SendVerificationEmail;
use App\Jobs\SetupMessengerInterface;
use App\Orders;
use App\PrebuiltAgent;
use App\Products;
use App\User;
use GuzzleHttp\Psr7\Response;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Exception;
use Illuminate\Support\Facades\Redis;

class AgentController extends Controller
{
    use DispatchesJobs, Queueable;

    private function setupAgentInterface($agent)
    {
        if (
            $agent->fb_access_token != null
            &&
            $agent->is_fb_webhook == true
            &&
            ($agent->page_subscription == false
                ||
                $agent->messenger_profile == false)
        ) {
            $this->dispatch((new SetupMessengerInterface($agent))
                ->onQueue(config('queueNames.messenger_updater')));
        }
    }

    private function checkAndUpdateAgentInfo($agent)
    {
        $this->setupAgentInterface($agent);

        // if updated_at is more than 10min fetch recent page info again
        // check if likes_count changed
        // if likes_count changed send old count and new count
        if (
            $agent->fb_access_token != null
            &&
            $agent->is_fb_webhook == true
        ) {
            $response = FacebookAPI::getPageMetric($agent);

            Log::info('Page metric: ');
            Log::info($response);
        }
    }

    private function checkAndUpdateAgents($agents)
    {
        foreach ($agents as $agent) {
            $this->checkAndUpdateAgentInfo($agent);
        }
    }

    /*
     * All agent lists
     * @return array
     *
     * api/agent_lists
     */
    public function agent_lists(Request $request)
    {
        try {
            $user = Auth::user();
            $agent_list = Agents::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
            // $this->checkAndUpdateAgents($agent_list);

            if (isset($agent_list)) {
                foreach ($agent_list as $agent) {
                    $agent->product_count = Products::totalUsableProducts($agent);
                    $agent->order_count = Orders::active_orders_counter($agent->id);
                }
            }

            $response = array('error' => false, 'agents' => $agent_list, 'user' => $user);
            return $response;
        } catch (Exception $e) {
            return ['error' => true, 'messsage' => $e->getMessage()];
        }
    }

    public function add_agent(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user == null)
                throw new Exception("Could not validate user, please varify that your have correct access rights.");

            $user_permitted_number_of_agents = $user->howmany_agents;
            $agent_count = $user->agents()->count();

            if ($agent_count >= $user_permitted_number_of_agents)
                throw new Exception("Its free to create any number of agent(s), we just need to know why. To create more agent(s) please contact us at ulka@ulkabd.com or call at +88 01726017250.");

            $prebuilt = PrebuiltAgent::where(['is_taken' => false])->first();

            if ($prebuilt == null)
                throw new Exception("Could not create a new agent at the moment, plz contact Usha service providers.");

            $agent = new Agents();
            $file =  $request->file('file');
            $agent->user_id = $user->id;
            $agent->agent_code = str_random(20);
            $agent->agent_name = $request->input('agent_name');
            $agent->apiai_dev_access_token = $prebuilt->apiai_dev_access_token;
            $agent->apiai_client_access_token = $prebuilt->apiai_client_access_token;
            $agent->is_default_intents_fetched = false;
            $agent->fb_verify_token = strtolower(str_random(5) . '_' . str_replace(' ', '', $request->input('agent_name')));

            if ($file != null) {
                $agent->image_path = $this->moveAgentImage($file);
            }

            $agent->save();

            $prebuilt->agent_id = $agent->id;
            $prebuilt->is_taken = true;
            $prebuilt->save();

            $message = array('error' => false, 'agents' => $agent, 'file' => $file);
            return $message;
        } catch (Exception $e) {
            $message = array('error' => true, 'message' => $e->getMessage());
            return $message;
        }
    }

    /*
     * Move uploaded product to products directory
     * @return boolean
     * @params FILE, code
     */
    private function moveAgentImage($file)
    {
        $user = Auth::user();
        $image_name = $file->getClientOriginalName();
        $extension = explode('.', $image_name);
        $extension = end($extension);
        $filter_name =  time() . '.' . $extension;
        $image_path = $user->id . '/' . Redis::get('agent_code_' . $user->id) . '/' . $filter_name;
        if (Storage::disk('uploads')->put($image_path, file_get_contents($file))) {
            return  $image_path;
        }
        return null;
    }


    /*
     * Update the access token from bots page
     * @return array
     * url: api/update_agent
     */
    public function update_agent(Request $request)
    {
        try {
            $agent = Agents::find($request->agent_id);
            $access_token = $request->fb_access_token != null ? $request->fb_access_token : null;

            if (
                $access_token !== null && strlen($access_token) > 10 &&
                $agent !== null /* && $agent->fb_access_token == null*/
            ) {

                $pRes = FacebookAPI::verifyTokenAndGetPageInfo($access_token);

                Log::info('Fb interface setup res: ');
                Log::info($pRes);

                if (isset($pRes['name']) && isset($pRes['id'])) {
                    Log::info('Access Token validation successful, stored page name and id.');

                    $agent->fb_access_token = $access_token;
                    $agent->fb_page_id = $pRes['id'];
                    $agent->fb_page_name = $pRes['name'];

                    $agent->page_subscription = false;
                    $agent->messenger_profile = false;

                    // For this line web.php facebook webhooks are commented
                    // if they are enabled comment out this line
                    $agent->is_fb_webhook = true;

                    $agent->save();

                    // TODO: Set this when apiai tokens are given, not here.
                    Agents::setAgentInCache($agent->agent_code, $agent);

                    $this->setupAgentInterface($agent);

                    return [
                        'error' => false,
                        'message' => 'Access Token validation successful.'
                    ];
                } else if (isset($pRes['error']) && isset($pRes['error']['message'])) {
                    return [
                        'error' => true,
                        'message' => new Exception($pRes['error']['message'])
                    ];
                } else {
                    return [
                        'error'   => true,
                        'message' => 'Unknown error.'
                    ];
                }
            } else {
                return [
                    'error'   => true,
                    'message' => 'Invalid request!'
                ];
            }
        } catch (Exception $e) {
            $message = array('error' => true, 'message' => 'There was a problem in updating the system!');
            return $message;
        }
    }

    /*
     * Get agent by ID
     * @return array
     * url api/get_agent
     */
    public function get_agent(Request $request)
    {

        try {
            $agent = Agents::find($request->agent_id);
            $message = array('error' => false, 'agent' => $agent);
            return $message;
        } catch (Exception $e) {
            $message = array('error' => true, 'message' => "Sorry! agent not found.");
            return $message;
        }
    }

    public function getAgentTrainingStatus(Request $request)
    {
        try {
            $status = Agents::getTrainingStatus();

            return [
                'error' => true,
                'status' => $status
            ];
        } catch (Exception $e) {
            $message = array(
                'error' => true,
                'message' => "Sorry! agent not found."
            );
            return $message;
        }
    }



    /*
     * Enable or disable agent
     * @params: boolean
     * return array
     */
    public function agent_engine_switcher(Request $request)
    {

        try {
            $agent = Agents::getCurrentAgent();

            if ($agent !== null) {

                $result = null;
                $switch  = $request->input("switch");

                if ($switch == "true") {
                    if ($agent->page_subscription == true) {
                        $result = ['switch' => true, 'request' => $switch, 'message' => "Agent is enabled"];
                    } else {
                        $response = FacebookAPI::subscribeWebhookToPageEvents($agent->fb_access_token);

                        if (isset($response['success']) && $response['success'] == true) {
                            $agent->page_subscription = true;
                            $agent->save();

                            $result = ['switch' => true, 'request' => $switch, 'message' => "Agent is enabled"];
                        } else {
                            throw new Exception('Could not enable agent, plz check network connection.');
                        }
                    }
                } else {

                    if ($agent->page_subscription == false) {
                        $result = ['switch' => false, 'message' => "Agent is disabled"];
                    } else {
                        $response = FacebookAPI::unSubscribeWebhookToPageEvents($agent->fb_access_token);

                        if (isset($response['success']) && $response['success'] == true) {
                            $agent->page_subscription = false;
                            $agent->save();

                            $result = ['switch' => false, 'message' => "Agent is disabled"];
                        } else {
                            throw new Exception('Could not disable agent, plz check network connection.');
                        }
                    }
                }

                $result['error'] = false;
                return $result;
            } else {
                throw new Exception('Could not find agent.');
            }
        } catch (Exception $ex) {
            return ["error" => true, "message" => $ex->getMessage()];
        }
    }
}
