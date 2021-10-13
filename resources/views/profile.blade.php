@extends('layouts.app')
@include('elements.dashboard',['sidebar'=>false,'navbar'=>true,'navbar_type'=>'bot_navbar'])
@section('content')
    <div class="container-fluid">
        <div class="row margin-0">
            <div class="col-lg-8 col-md-8 col-lg-offset-2">
                <div class="card-bg profile-page">
                    <div class="row">
                        <div class="col-lg-3 col-md-3">
                            <div class="profile-image">
                                <img src="{{ asset("images/create_bot.png") }}" class="img-responsive">
                                <i class="fa fa-camera"></i>
                            </div>
                        </div>
                        <div class="col-lg-9 col-md-9">
                            <div class="row">
                                <div class="col-lg-12 col-md-12">
                                    <div class="pull-left"><h1> {{__('pages.profile.card.heading')}}</h1></div>
                                    <div class="pull-right text-right"><a href="{{ url('bots') }}" class="btn btn-admin btn-admin-info">{{__('pages.profile.card.agentButton')}} </a></div>
                                </div>
                            </div>
                            <form method="post" action="{{ url('profile/store') }}">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label for="formGroupExampleInput">{{__('pages.profile.card.form.company.label')}}</label>
                                    <input type="text" class="form-control input-from-com" id="formGroupExampleInput" name="business_name" placeholder="{{__('pages.profile.card.form.company.placeholder')}}" value="{{ $companyIdentity->business_name }}" >
                                    <input type="hidden" name="business_id" value="{{ $companyIdentity->id }}" >
                                </div>
                                <div class="form-group">
                                    <div class="col-md-6 padding-left-0">
                                        <label for="formGroupExampleInput2">{{__('pages.profile.card.form.firstName.label')}} </label>
                                        <input type="text" class="form-control input-from" id="first_name" name="first_name" placeholder="{{__('pages.profile.card.form.firstName.placeholder')}}" value="{{ $user->first_name }}">
                                        @if ($errors->has('first_name')) <p class="alert alert-danger">{{ $errors->first('first_name') }}</p> @endif
                                    </div>
                                    <div class="col-md-6 padding-right-0">
                                        <label for="formGroupExampleInput2">{{__('pages.profile.card.form.lastName.label')}} </label>
                                        <input type="text" class="form-control input-from" id="last_name" name="last_name" placeholder="{{__('pages.profile.card.form.lastName.placeholder')}}" value="{{ $user->last_name }}">
                                        @if ($errors->has('last_name')) <p class="alert alert-danger">{{ $errors->first('last_name') }}</p> @endif
                                    </div>
                                    <div class="clearfix"></div>
                                </div>

                                <div class="form-group">
                                    <label for="formGroupExampleInput2">{{__('pages.profile.card.form.address.label')}}</label>
                                    <textarea class="form-control input-from" id="address" name="address" rows="3"> {{ $companyIdentity->address }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="formGroupExampleInput2">{{__('pages.profile.card.form.mobile.label')}}</label>
                                    <input type="text" class="form-control input-from" name="mobile_no" placeholder="{{__('pages.profile.card.form.mobile.placeholder')}}" value="{{ $user->mobile_no }}">
                                    @if ($errors->has('mobile_no')) <p class="alert alert-danger">{{ $errors->first('mobile_no') }}</p> @endif
                                </div>
                                <div class="form-group">
                                    <label for="formGroupExampleInput2">{{__('pages.profile.card.form.phone.label')}}</label>
                                    <input type="text" class="form-control input-from" name="phone_no" placeholder="{{__('pages.profile.card.form.phone.placeholder')}}" value="{{ $user->phone_no }}">
                                    @if ($errors->has('phone_no')) <p class="alert alert-danger">{{ $errors->first('phone_no') }}</p> @endif
                                </div>
                                <div class="form-group">
                                    <label for="formGroupExampleInput2">{{__('pages.profile.card.form.primaryEmail.label')}}</label>
                                    <input type="email" readonly class="form-control input-from" name="email" placeholder="{{__('pages.profile.card.form.primaryEmail.placeholder')}}" value="{{ $user->email }}">
                                </div>
                                <div class="form-group">
                                    <label for="formGroupExampleInput2">{{__('pages.profile.card.form.secondaryEmail.label')}}</label>
                                    <input type="email" class="form-control input-from" id="email_id" name="secondary_email" placeholder="{{__('pages.profile.card.form.secondaryEmail.placeholder')}}" value="{{ $user->secondary_email }}">
                                    @if ($errors->has('secondary_email')) <p class="alert alert-danger">{{ $errors->first('secondary_email') }}</p> @endif
                                </div>
                                <div class="form-group">
                                    <input type="submit" name="submit" class="btn btn-theme btn-theme-success" value="{{__('common.buttons.save')}}">
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
