@extends('layouts.app')
@section('stylesheet')
    <link href="{{ asset('css/sa_app.css') }}" rel="stylesheet">
@endsection

@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')
    {{--react-active-class = configDashboard--}}
    <div class="container-fluid" id="configDashboards">
        <div class="row margin-0 page-section">
            <div class="col-lg-12 col-md-12 col-sm-12 zero-padding">
                <div class="card-bg">
                    <div class="row configure">
                        <div class="col-sm-10 margin-top">
                            <div class="header-title">{{__('pages.configure.card.title')}}</div>
                        </div>
                        <div class="col-sm-2 margin-top-switch">
                            <div class="switch text-right">
                                <input id="botEngineSwitch" @if($agent_status) checked="checked"  @endif class="cmn-toggle cmn-toggle-round-flat bot_enable_disable_checkbox" type="checkbox">
                                <label for="botEngineSwitch"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function addOption() {
            console.debug($("#option").val());
            var $option = $("#option").val();
            var $selected = $(".selected-options");

            $input = "<input type='hidden' name='" + $option + "' value='" + $option + "' />";
            $html = "<div class='alert alert-dismissible fade in' role='alert'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button>"+$option+$input+"</div>";

            $selected.append($html);
        }
    </script>
@endsection
