@extends('layouts.app')
@section('stylesheet')
    <link href="{{ asset('css/sa_app.css') }}" rel="stylesheet">
@endsection

@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')
    {{--react-active-class = configDashboard--}}
    <div class="container-fluid" id="manageDashboard">
        <div class="row margin-0">
            <div class="coming-soon">
                <h3 class="coming-soon-title"> {{__('pages.manage.title')}} </h3>
                <p> {{__('pages.manage.paragraph')}} </p>
            </div>
        </div>

    </div>
@endsection

@section('script')

@endsection
