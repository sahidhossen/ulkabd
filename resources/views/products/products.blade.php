@extends('layouts.app')

@section('stylesheet')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="{{ asset('css/react-table.css') }}">
    <link rel="stylesheet" href="{{ asset('css/react-select/react-select.css') }}">
@endsection

@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')
    <div class="container-fluid zero-padding">
        <div class="row margin-0" id="product_body">
            {{--React call--}}
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
@endsection
