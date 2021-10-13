@extends('layouts.auth')

@section('content')
    <div class="panel-bg">
        <div class="margin-padding-0 left-panel">
            <div class="top-image">
                <a class="site-logo" href="{{ url('/') }}">
                    <img class="img-responsive" src="{{ asset("images/usha-login-logo.png")  }}" alt="ulka" />
                </a>
            </div>
            <div class="bottom-image">
                <a class="site-logo">
                    <img class="img-responsive" src="{{ asset("images/login-img.png")  }}">
                </a>
            </div>
        </div>
        <div class="margin-padding-0 right-panel">
            <div class="panel-body">
                <div class="col-md-12">
                    <div class="login-title">{{__('pages.login.panel.title')}}</div>
                    <p class="login-description">{{__('pages.login.panel.description')}}</p>
                </div>
                <div class="col-md-12">
                    <form role="form" class="form-login form" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <input id="email" type="text" class="form-control" name="email" placeholder="{{__('pages.login.form.placeholders.email')}}" value="{{ old('email') }}" required autofocus>

                            @if ($errors->has('email'))
                                <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                            @endif
                            @if ($errors->has('user_name'))
                                <span class="help-block">
                                            <strong>{{ $errors->first('user_name') }}</strong>
                                        </span>
                            @endif
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <input id="password" type="password" class="form-control" placeholder="{{__('pages.login.form.placeholders.password')}}" name="password" required>

                            @if ($errors->has('password'))
                                <span class="help-block">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> {{__('pages.login.form.checkbox.label')}}
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-login btn-lg btn-block">
                            {{__('pages.login.form.loginButton')}}
                            </button>
                        </div>
                    </form>
                </div>
                <div class="text-center">
                    <a class="forgot-pass" href="{{ route('password.request') }}">
                    {{__('pages.login.form.links.forgotPassword')}}
                    </a>
                    {{--<p class="sing-title">--}}
                        {{--Don't have an account?--}}
                        {{--<a class="sing-up" title="Go to Sign Up" href="{{ route('register') }}">{{__('pages.login.form.signupButton')}}</a>--}}
                    {{--</p>--}}
                </div>
            </div>
        </div>
    </div>


@endsection
