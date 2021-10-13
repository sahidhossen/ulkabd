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
                    <div class="login-title">{{__('pages.password.email.panel.title')}}</div>
                    <p class="login-description">{{__('pages.password.email.panel.description')}}</p>
                </div>
                <div class="col-md-12">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form class="form-login form" role="form" method="POST" action="{{ route('password.email') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="control-label">{{__('pages.password.email.panel.form.label')}}</label>

                            <input id="email" placeholder="{{__('pages.password.email.panel.form.placeholder')}}" type="email" class="form-control" name="email" value="{{ old('email') }}" required>

                            @if ($errors->has('email'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-login btn-lg btn-block">
                            {{__('pages.password.email.panel.form.sendButton')}}
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-12 text-center">
                    <p class="sing-title">

                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
