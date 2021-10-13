@extends('layouts.app')

@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')
<div class="container-fluid" id="mainAiView">
        <div class="inner-ai-dashboard">
            <div class="ai-title-area">
                <h3 class="title text-center"> {{__('pages.ai.title')}} </h3>
            </div>
             
            <div class="ai-steps">
                <!-- <div class="step step_1">

                    <a href="{{ url('/'.$agent->agent_code.'/configure') }}">
                        <div class="step-icon"> <i class="fa fa-gear"></i></div>
                        <div class="step-details"> <h1> Configuration </h1></div>
                    </a>
                </div> -->
                <div class="step step_2">

                    <a href="{{  url('/'.$agent->agent_code.'/categories') }}">
                        <div class="step-icon"> <i class="fa fa-snowflake-o"></i></div>
                        <div class="step-details"> <h1> {{__('pages.ai.links.intent')}} </h1></div>
                    </a>
                </div>
                <div class="step step_3">

                    <a href="{{ url('/'.$agent->agent_code.'/products') }}">
                        <div class="step-icon"> <i class="fa fa-braille"></i></div>
                        <div class="step-details"> <h1> {{__('pages.ai.links.entities')}} </h1></div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
