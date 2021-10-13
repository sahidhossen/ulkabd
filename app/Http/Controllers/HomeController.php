<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Agents;
use Illuminate\Support\Facades\Auth;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

class HomeController extends Controller
{
    /**
     * Show the application front-end pages .
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
    }

    public function index()
    {
        $data = [
            'appId' => config('agent.facebook_protocols.app_id'),
            'fb_api_version' => config('agent.facebook_protocols.v_api')
        ];

        $link = $_SERVER['HTTP_HOST'];
        $all = '.com';
        $japan = '.co.jp';

        // .com for rendering in english
        if (strpos($link, $all) === true) {
            return view("welcome")->with($data);
        }
        // .co.jp for rendering in japanese
        elseif (strpos($link, $japan) === true) {
            return view("ja-welcome")->with($data);
        }
        // by default render in english
        else {
            return view("welcome")->with($data);
        }
    }

    /*
     * Privacy page
     */
    public function privacy()
    {
        return view("privacy");
    }

    /*
     * terms
     */
    public function terms()
    {
        return view("terms");
    }
}
