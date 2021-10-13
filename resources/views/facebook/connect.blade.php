@extends('layouts.app')
@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')
    <div class="container-fluid">
        @if( Session::get('error') )
            <div class="row margin-0 dashboard-widget">
                <p class="alert alert-danger"> {{ Session::get('error')  }}</p>
            </div>
        @endif
        <div class="row margin-0 dashboard-widget">

            <div class="fb-connection-btn @if($agent->is_apiai_fb_integration == true || $all_user_pages != null ) hide @endif">
                <h2 class="title text-center" style=" font-weight: 100;"> {{__('pages.fbConnect.dashboard.title')}} </h2>
                <div align="center">
                    <div
                            class="fb-login-button"
                            data-max-rows="1"
                            data-size="large"
                            data-button-type="login_with"
                            data-use-continue-as="true"
                            onlogin="checkLoginState();"
                            scope="<?php echo implode(', ', $permissions) ?>"
                    ></div>
                </div>
            </div>

            @if( $agent->is_apiai_fb_integration == false )
                @if($all_user_pages != null )
                    <div class="facebook_pages">
                        <h4 class="title text-center"> {{__('pages.fbConnect.fbpages.titles.list')}} </h4>
                        <ul>
                            @foreach( $all_user_pages['data'] as $page )
                            <li>
                                <div class="fb_name"> {{ $page['name'] }}</div>
                                <div class="fb_active_btn"><a href="#" data-fb_page_id="{{ $page['id'] }}" data-fb_page_name="{{ $page['name'] }}" data-page_access_token="{{ $page['access_token'] }}" class="btn btn-primary btn-md active_fb_page"> {{__('pages.fbConnect.fbpages.buttons.connect')}} </a></div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @else
                <div class="facebook_pages">
                    <h4 class="title text-center"> {{__('pages.fbConnect.fbpages.titles.active')}} </h4>
                    <ul>
                        <li>
                            <div class="fb_name"> {{ $agent->fb_page_name }}</div>
                            <div class="fb_active_btn"><a href="#" data-fb_page_id="{{ $agent->fb_page_id }}" data-fb_page_name="{{ $agent->fb_page_name }}" data-page_access_token="{{ $agent->fb_access_token }}" class="btn btn-primary btn-md deactivate_fb_page"> {{__('pages.fbConnect.fbpages.buttons.disconnect')}} </a></div>
                        </li>

                    </ul>
                </div>
            @endif
        </div>
    </div>


@endsection

@section("script")
    <script>
        let appId = <?php echo json_encode($appId) ?>;
        let version = <?php echo json_encode($fb_api_version) ?>;
        let agent = <?php echo json_encode($agent) ?>;
        let all_user_pages = <?php echo json_encode($all_user_pages) ?>;

        if (agent.is_apiai_fb_integration == false && all_user_pages == null) {
            function statusChangeCallback(response, redirect = false) {
                // console.debug('statusChangeCallback');
                // console.debug(response);
                
                if (response.status === 'connected') {
                    if ( redirect == true ) redirectToConnectPagesView(response);
                }
            }

            function checkLoginState() {
                FB.getLoginStatus(function(response) {
                    statusChangeCallback(response, true);
                });
            }

            window.fbAsyncInit = function() {
                if (appId != null && version != null) {
                    FB.init({
                        appId      : appId,
                        xfbml      : true, // parse social plugins on this page
                        version    : version
                    });

                    FB.getLoginStatus(function(response) {
                        statusChangeCallback(response);
                    });
                }
            };

            // Load the SDK asynchronously
            (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = "https://connect.facebook.net/en_US/sdk.js";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));

            function redirectToConnectPagesView(fbResponse) {
                // console.debug('Welcome!  Fetching your information.... ');
                // FB.api('/me', function(response) {
                //     console.debug('Successful login for: ' + response.name);
                // });
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: 'POST',
                    url: '/facebook_login_callback',
                    data: {
                        fb_token: fbResponse.authResponse.accessToken
                    },
                    success: function(response){
                        // $("#msg").html(data.message);
                        // console.debug(`Response: ${response}`);
                        if (response.redirect_url != null)
                            window.location = response.redirect_url;
                    },
                    error: (error) => {
                        console.debug(`Error: ${error.message}`);
                    }
                });
            }
        }

    </script>
@endsection
