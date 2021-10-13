@extends('layouts.auth')

@section('content')">
            <div class="panel panel-default">
                <div class="panel-heading">{{__('pages.password.reset.panel.heading')}}</div>

                <div class="panel-body">
                   <div class="col-md-8 col-md-offset-2">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form class="form-login form" role="form" method="POST" action="{{ route('password.request') }}">
                            {{ csrf_field() }}

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">{{__('pages.password.reset.panel.form.email.label')}}</label>

                                <div class="col-md-6">
                                    <input id="email" placeholder="{{__('pages.password.reset.panel.form.email.placeholder')}}" type="email" class="form-control" name="email" value="{{ $email or old('email') }}" required autofocus>

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label for="password"  class="col-md-4 control-label">{{__('pages.password.reset.panel.form.password.label')}}</label>

                                <div class="col-md-6">
                                    <input id="password" placeholder="{{__('pages.password.reset.panel.form.password.placeholder')}}" type="password" class="form-control" name="password" required>

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                <label for="password-confirm" class="col-md-4 control-label">{{__('pages.password.reset.panel.form.confirm.label')}}</label>
                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>

                                    @if ($errors->has('password_confirmation'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password_confirmation') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                    {{__('pages.password.reset.panel.form.resetButton')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
@endsection
