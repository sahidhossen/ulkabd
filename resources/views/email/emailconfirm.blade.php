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
                    <div class="login-title">{{__('pages.email.confirm.panel.title')}}</div>
                    <p class="login-description"> {{__('pages.email.confirm.panel.paragraphDescription')}} </p>
                </div>
                <div class="col-md-12">
                    <p>
                    {{__('pages.email.confirm.panel.paragraph')}}
                        <a class="btn btn-admin btn-admin-white" href={{ route('login') }}>{{__('pages.email.confirm.panel.link')}}</a>
                    </p>
                 </div>

            </div>
        </div>
    </div>


@endsection
