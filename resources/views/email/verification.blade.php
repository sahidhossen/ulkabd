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
                    <div class="login-title">{{__('pages.emailVerify.verification.panel.title')}} </div>
                </div>
                <div class="col-md-12">
                   <p> {{__('pages.emailVerify.verification.panel.paragraph')}} </p>
                    <p> <small> {{__('pages.emailVerify.verification.panel.paragraphSmall')}} </small>
                    <form action="{{ url('/resend_verification_mail') }}" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" value="{{$user->email_token}}" name="email_token">
                        <button class="btn btn-default">{{__('pages.emailVerify.verification.panel.form.resendButton')}}</button>
                    </form>
                    </p>
                </div>

            </div>
        </div>
    </div>
@endsection
