<?php

namespace App\Http\Controllers\Auth;

use App\BusinessIdentity;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Role;
use App\Jobs\SendVerificationEmail;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use League\Flysystem\Exception;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/bots';


    /*
     * Facebook app_id and app_secrete
     */
    protected $app_id;
    protected $app_secret;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->app_id = config('agent.facebook_protocols.app_id');
        $this->app_secret = config('agent.facebook_protocols.app_secret');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'user_name' => 'required|unique:users|max:255',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'business_name' => 'required|max:255',
            'mobile_no' => 'required|numeric|min:11,max:13',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',

        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'user_name' => $data['user_name'],
            'email' => $data['email'],
            'mobile_no' => $data['mobile_no'],
            'password' => bcrypt($data['password']),
            'email_token' => base64_encode($data['email'])
        ]);

        $rootRole = Role::where('name','=','admin')->first();
        $user->attachRole($rootRole);

        $business = BusinessIdentity::create([
            'business_name'=> $data['business_name'],
            'address'=> $data['address'],
            'user_id'=>$user->id
        ]);

        return $user;

    }

    public function register_by_facebook(Request $request){
        session_start();
        $fb = new Facebook([
            'app_id' => $this->app_id,
            'app_secret' => $this->app_secret,
            'default_graph_version' => config('agent.facebook_protocols.v_api'),
            'default_access_token' => $this->app_id.'|'.$this->app_secret
        ]);

        $helper = $fb->getRedirectLoginHelper();
//        dd( $helper->getAccessToken() );
        try {
            if (Session::has('fb_access_token')) {
                Session::forget('fb_access_token');
            }

            $accessToken = $helper->getAccessToken();
            if ($accessToken == null) throw new Exception("None or invalid access token!");

//            $response = $fb->get('/me?fields=accounts,name,email');
//            $graphObject = $response->getDecodedBody();

            // session(['fb_access_token' => $accessToken->getValue()]);
            $userInformation = $this->get_fb_user_information($accessToken->getValue());

            dd( $userInformation );

        }
        catch(FacebookResponseException $e) {
            // When Graph returns an error
            Session::flash('error', 'Facebook SDK returned an error: ' . $e->getMessage());
        }
        catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            Session::flash('error', 'Facebook SDK returned an error: ' . $e->getMessage());
        }
        catch (Exception $e) {
            return redirect( route("front_page") );
        }

    }

    private function get_fb_user_information( $fb_access_token ){
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

            return $graphObject;

        }
        catch (FacebookResponseException $e) {
            Session::flash('error', 'Facebook SDK returned an error: ' . $e->getMessage());
            return null;
        }
        catch( Exception $e ) {
            return null;
        }

    }

    /*
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response
    */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        event(new Registered($user = $this->create($request->all())));
        dispatch(new SendVerificationEmail($user));
        return view('email.verification')->with('user', $user );
    }


    public function showRegistrationForm()
    {
        return redirect('login');
    }

    public function showUshaRegister(){
        return view('auth.register');
    }
}
