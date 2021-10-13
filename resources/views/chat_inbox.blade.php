@extends('layouts.app')
@section('stylesheet')
    <link href="{{ asset('css/sa_app.css') }}" rel="stylesheet">
@endsection

@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')
    <div id="chat_inbox"></div>
@endsection