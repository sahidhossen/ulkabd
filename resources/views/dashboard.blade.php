@extends('layouts.app')
@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('css/react-table.css') }}">
@endsection
@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')
<audio id='audio' controls style="display: none">
    <source src="{{asset('files/new-order-alert.mp3')}}" type="audio/mp3" />
</audio>
<div class="container-fluid" id="mainDashboard">
</div>
@endsection
