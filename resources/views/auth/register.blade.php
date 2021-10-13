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
                    <div class="login-title">{{__('pages.register.panel.title')}}</div>
                    <p class="login-description">{{__('pages.register.panel.description')}}</p>
                </div>
                <div class="col-md-12">
                    <form class="form-login form" role="form" method="POST" action="{{ route('register') }}">
                        {{ csrf_field() }}
                        <div class="form-group{{ $errors->has('business_name') ? ' has-error' : '' }} required">
                            <input id="business_name" type="text" class="form-control" placeholder="{{__('pages.register.panel.form.placeholders.company')}}" name="business_name" value="{{ old('business_name') }}" required autofocus>

                            @if ($errors->has('business_name'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('business_name') }}</strong>
                                    </span>
                            @endif
                        </div>
                        <div class="form-group{{ $errors->has('first_name') ? ' has-error' : '' }} required">
                            <input id="last_name" type="text" class="form-control" placeholder="{{__('pages.register.panel.form.placeholders.firstName')}}" name="first_name" value="{{ old('first_name') }}" required>

                            @if ($errors->has('first_name'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('first_name') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="form-group{{ $errors->has('last_name') ? ' has-error' : '' }} required">
                            <input id="last_name" type="text" class="form-control" placeholder="{{__('pages.register.panel.form.placeholders.lastName')}}" name="last_name" value="{{ old('last_name') }}" required>

                            @if ($errors->has('last_name'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('last_name') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }} required">
                            <input id="email" type="email" class="form-control" placeholder="{{__('pages.register.panel.form.placeholders.email')}}" name="email" value="{{ old('email') }}" required>

                            @if ($errors->has('email'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="form-group{{ $errors->has('user_name') ? ' has-error' : '' }} required">
                            <input id="user_name" type="text" class="form-control" placeholder="{{__('pages.register.panel.form.placeholders.userName')}}" name="user_name" value="{{ old('user_name') }}" required>

                            @if ($errors->has('user_name'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('user_name') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="form-group{{ $errors->has('mobile_no') ? ' has-error' : '' }} required">
                            <input id="mobile_no" type="text" class="form-control" placeholder="{{__('pages.register.panel.form.placeholders.phone')}}" name="mobile_no" value="{{ old('mobile_no') }}" required>

                            @if ($errors->has('mobile_no'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('mobile_no') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <textarea id="address" class="form-control" placeholder="{{__('pages.register.panel.form.placeholders.address')}}" name="address" rows="3">{{ old('address') }}</textarea>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }} required">
                            <input id="password" type="password" class="form-control" placeholder="{{__('pages.register.panel.form.placeholders.password')}}" name="password" required>

                            @if ($errors->has('password'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="form-group required">
                            <input id="password-confirm" type="password" class="form-control" placeholder="{{__('pages.register.panel.form.placeholders.confirmPassword')}}" name="password_confirmation" required>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-login btn-lg btn-block">
                            {{__('pages.register.panel.form.signupButton')}}
                            </button>
                        </div>
                    </form>
                </div>
                <div class="text-center">
                    <p class="sing-title">{{__('pages.register.panel.loginOption.singTitle')}} <a class="sing-up" title="{{__('pages.register.panel.loginOption.tooltip')}}" href="{{ route('login') }}">{{__('pages.register.panel.loginOption.loginButton')}}</a></p>
                </div>
            </div>
        </div>
    </div>

@endsection
