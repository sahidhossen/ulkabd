@extends('layouts.app')

@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')

<div class="container page-body">
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading"> {{__('pages.uploadCSV.panel.heading')}}</div>
                    <div class="panel-body">
                       <h1> {{__('pages.uploadCSV.panel.body.heading')}}</h1>
                        <form method="post" action="{{ url('/upload/csv_upload_process') }}" enctype="multipart/form-data">


                            <div class="fallback">
                                <input name="csv_file" type="file"  />
                            </div>

                            <input type="submit" class="btn btn-primary" value="{{__('pages.uploadCSV.panel.body.uploadButton')}}">
                            {{ csrf_field() }}
                        </form>

                    </div>
                </div>
            </div>
        </div>
</div>

@endsection
