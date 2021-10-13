<?php

namespace App\Http\Controllers;

use App\Jobs\SendVerificationEmail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\Flysystem\Exception;

class UserController extends Controller
{
    private $returnView;

    /*
     * Resend verification email to the user email
     */
    public function resend_verification_mail(Request $request) {
        try{
            $email_token = $request->input('email_token');
            $user = null;
            $this->returnView = false;

            if (isset($email_token)) {
                $user = User::where('email_token', $email_token)->first();
                $this->returnView = true;
            }
            else {
                $user = Auth::user();
            }

            if ($user == null) throw new Exception("Could not find user.");

            if( $user->email_token == null ){
                $user->email_token = base64_encode($user->email);
                $user->save();
            }

            dispatch(new SendVerificationEmail($user));

            if ($this->returnView == true) {
                return view('email.verification')->with('user', $user );
            }
            else {
                return [
                    'error'=>false,
                    'user'=>$user
                ];
            }
        }
        catch(Exception $e ) {
            return [
                'error'=>true,
                'message'=>$e->getMessage()
            ];
        }
    }


    /**
     * Handle a registration request for the application
     */
    public function verify($token)
    {
        $user = User::where('email_token',$token)->first();
        $user->verified = 1;
        if($user->save()) {
            return view('email.emailconfirm',['user'=>$user]);
        }
    }

}
