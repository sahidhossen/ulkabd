<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // Bot webhooks
//        '/*/webhook',
        '/apiaiwebhook',

        //Bot web extension APIs
        '/bot_web_ext/profile_cart',
        '/bot_web_ext/cart_update_checkout',
        '/bot_web_ext/user_profile'
    ];
}
