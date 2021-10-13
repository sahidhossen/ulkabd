@extends('layouts.app')


@section('stylesheet')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="{{ asset('css/node_modules.css') }}">
    <link rel="stylesheet" href="{{ asset('css/react-tagsinput/react-tagsinput.css') }}">
    {{--<link rel="stylesheet" href="{{ asset('css/category.css') }}">--}}
@endsection

@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')
    <div class="container-fluid">
        <div class="row margin-0">
            <div class="col-lg-12 col-md-12 zero-padding">
                <div class="dashboard-widget">
                    <h3> {{__('pages.category.dashboard.title')}} </h3>
                    <div class="category_chain_main" id="category_body">

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
@endsection
