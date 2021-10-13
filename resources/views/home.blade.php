@extends('layouts.app')
@section('stylesheet')
    <link href="{{ asset('css/fullcalendar.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/bots.css') }}">
@endsection

@include('elements.dashboard',['sidebar'=>false,'navbar'=>true,'navbar_type'=>'bot_navbar'])

@section('content')
<div class="container-fluid">
    <div class="row margin-0">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div id="mainBot"></div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
