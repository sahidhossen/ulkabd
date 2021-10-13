<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use League\Flysystem\Exception;

class ProfileController extends Controller
{
    //
    public function profile(){
        $user = Auth::user();
        $companyIdentity = $user->business_identity;
        return view('profile')->with( ["user"=>$user,'companyIdentity'=>$companyIdentity] );
    }


    /*
     * Store the profile and validate it
     */
    public function store( Request $request ){
//        dd($request->all());
        try{
            $user = Auth::user();

            $this->validate($request, [
                'first_name' => 'required|min:2|max:255',
                'last_name' => 'required|max:255',
                'business_name' => 'required|max:255',
                'mobile_no' => 'required|numeric|min:11,max:13',
                'phone_no' => 'sometimes|nullable|numeric|min:11,max:13',
                'secondary_email' => 'sometimes|nullable|email|max:3',
            ]);

            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->mobile_no = $request->input('mobile_no');
            $user->phone_no = $request->input('phone_no');
            $user->secondary_email = $request->input('secondary_email');

            $user->save();

            $user->business_identity->address = $request->input('address');
            $user->business_identity->business_name = $request->input('business_name');
            $user->business_identity->save();

            return back()->withInput();
        }catch(Exception $ex ){
            return response()->view('errors.403', array('message'=>$ex->getMessage()), 403);
        }

    }
}
