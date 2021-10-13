<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset("favicon.ico") }}"/>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset("favicon.ico") }}"/>
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    {{--Font awesome--}}
    <link rel="stylesheet" href="{{ asset('css/font-awesome.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.3.0/min/dropzone.min.css">

    @yield('stylesheet')
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body class="dashboard" @if (!Auth::guest()) data-user_id="{{ Auth::user()->id }}" @endif id="loginDashboard">

<div id="app" class="@if( isset($sidebar) && ($sidebar==true)) toggled @endif ">
@if( isset($sidebar) && ($sidebar==true))
@section('sidebar')
    @include('elements.sidebar')
@endsection

@endif
