@extends('layouts.app')

@section('stylesheet')
    <link href="{{ asset('css/sa_app.css') }}" rel="stylesheet">
@endsection

@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')
    <div class="container-fluid">
        <div class="row margin-0">
            <div class="col-lg-12 col-md-12 zero-padding">
                <div class="dashboard-widget">
                    <h3> Change Plan </h3>
                    <div id="change_plan">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection