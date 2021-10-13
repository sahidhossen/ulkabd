<nav class="navbar navbar-default navbar-static-top">
    <div class="container-fluid">
        <div class="row margin-0">
            <div class="col-lg-12 col-md-12">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/bots') }}">

                        @if( isset($sidebar) && ($sidebar==true))
                            <span class="menu-toggle"> <i class="fa fa-bars"></i></span>
                        @endif
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <ul class="nav navbar-nav navbar-left bot-lists">
                        <li class="dropdown">
                            @if(count( $agents ) > 1 )
                                <a href="#" class="dropdown-toggle bot-title" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">

                                    <img class="bot-logo img-circle" src="{{ asset( ($active_agent->image_path == null ) ? "images/usha.png" : "/uploads/".$active_agent->image_path ) }}" alt="Avatar">
                                        {{ $active_agent->agent_name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu">

                                    @foreach( $agents as $agent )
                                        @if( $agent->id != $active_agent->id )
                                            <li>
                                                <a class="list-bot-title" href="{{ url( '/dashboard/'.$agent->agent_code ) }}">
                                                        <img class="list-bot-logo img-circle" src="{{ asset( ($agent->image_path == null ) ? "images/usha.png" : "/uploads/".$agent->image_path ) }}" alt="{{ $agent->agent_name }}"> {{ $agent->agent_name }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                    @else
                                        <a href="{{ url('/dashboard/'.$active_agent->agent_code)  }}">
                                            <img class="bot-logo img-circle" src="{{ asset( ($active_agent->image_path == null ) ? "images/usha.png" : "/uploads/".$active_agent->image_path ) }}" alt="Avatar">
                                            {{ $active_agent->agent_name }}
                                        </a>
                                    @endif
                                </ul>
                        </li>
                    </ul>
                    <!-- end of bot dropdown -->
                {{--<h1 class='page-title navbar-text navbar-center'>Dashboard</h1>--}}
                <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right user-nav">
                        <!-- language selector dropdown starts -->
                        @php 
                            $locale = app()->getLocale(); 
                            $languages = config('languages.languages');
                        @endphp
                        
                        <li class="dropdown">
                            <a href="#" style="margin-top:-2px;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                {{$languages[$locale]}}
                                <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                            @foreach ($languages as $locale => $language)
                                <li>
                                    <a href="/locale/{{$locale}}">{{$language}}</a>
                                </li>
                            @endforeach
                            </ul>
                        </li>
                        <!-- language selector dropdown ends -->

                     <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ route('login') }}">{{__('common.navbar.links.login')}}</a></li>
{{--                            <li><a href="{{ route('register') }}">Register</a></li>--}}
                        @else

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->last_name }}
                                    <img class="user-avatar img-circle" src="{{ asset('images/no-profile.png') }}" alt="{{ Auth::user()->last_name }}">
                                    <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li class="{{ Request::segment(1) === 'profile' ? 'active' : null }}" ><a href="{{ url('/profile') }}"><i class="fa fa-user-o" aria-hidden="true"></i> {{__('common.navbar.dropdown.menus.profile')}} </a></li>
                                    <li >
                                        <a href="{{ route('logout') }}"
                                           onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">

                                            <i class="fa fa-sign-out" aria-hidden="true"></i>
                                            {{__('common.navbar.dropdown.menus.logout')}}
                                        </a>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>

                        @endif
                    </ul>

                    <div class="pull-right fb_messenger_btn">
                        <a target="_blank" class="btn btn-admin btn-admin-white @if(!$active_agent->fb_page_id) disabled @endif " href="https://m.me/{{ $active_agent->fb_page_id }}"> <span><i class="fa fa-telegram"></i></span> {{__('common.buttons.messenger')}} </a>
                    </div>

                    <div class="pull-right trainAgentBtn" id="trainAgentAction">
                       {{--call reactjs --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>