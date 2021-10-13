@extends('layouts.app')

@section('stylesheet')
    <link href="{{ asset('css/fullcalendar.css') }}" rel="stylesheet">
@endsection

@include('elements.dashboard',['sidebar'=>true,'navbar'=>true])

@section('content')
    <div class="container-fluid hide">
        <div class="row margin-0 hide">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <div class="dashboard-widget">
                            <div class="row bottom-margin-15px">
                                <div class="col-md-8 pull-left top-padding-15px table-caption">Schedule List</div>
                                <div class="col-md-4 pull-left text-right">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="dashboard-widget list schedules-list">
                            <div class="row bottom-margin-15px">
                                <div class="col-md-8 pull-left top-padding-15px">
                                    <div class="bot-calender">
                                        <div id='calendar'></div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-12 pull-left schedule-details clearfix">
                                        <div class="broadcast-day">{{ date("l") }}</div>
                                        <div class="broadcast-month-date">{{ date("F j") }}</div>
                                        <div class="broadcast-time">{{ date("h:i A") }}</div>
                                        <div class="broadcast-title bottom-margin-30px">Single Image</div>

                                        <div class="broadcast-events clearfix">
                                            <div class="pull-left">
                                                <a class="btn btn-primary btn-broadcast" title="broadcast" href="#">Broadcast</a>
                                                <a class="btn btn-primary btn-broadcast" title="repeat" href="#">Repeat</a>
                                            </div>
                                            <div class="pull-right text-right">
                                                <a class="btn btn-primary btn-broadcast" title="pause" href="#"><i class="fa fa-pause" aria-hidden="true"></i></a>
                                                <a class="btn btn-primary btn-broadcast" title="close" href="#"><i class="fa fa-close" aria-hidden="true"></i></a>
                                            </div>
                                        </div>
                                        <div class="row broadcast-preview  clearfix top-margin-15px">
                                            <div class="col-md-4 col-sm-4"><div class="preview-item thumbnail"><img src="{{ asset("images/product-images/1002.jpg")  }}" alt=""></div></div>
                                            <div class="col-md-4 col-sm-4"><div class="preview-item thumbnail"><img src="{{ asset("images/product-images/1002.jpg")  }}" alt=""></div></div>
                                            <div class="col-md-4 col-sm-4"><div class="preview-item thumbnail"><img src="{{ asset("images/product-images/1002.jpg")  }}" alt=""></div></div>
                                            <div class="col-md-4 col-sm-4"><div class="preview-item thumbnail"><img src="{{ asset("images/product-images/1002.jpg")  }}" alt=""></div></div>
                                            <div class="col-md-4 col-sm-4"><div class="preview-item thumbnail"><img src="{{ asset("images/product-images/1002.jpg")  }}" alt=""></div></div>
                                            <div class="col-md-4 col-sm-4"><div class="preview-item thumbnail"><img src="{{ asset("images/product-images/1002.jpg")  }}" alt=""></div></div>
                                            <div class="col-md-4 col-sm-4"><div class="preview-item thumbnail"><img src="{{ asset("images/product-images/1002.jpg")  }}" alt=""></div></div>
                                            <div class="col-md-4 col-sm-4"><div class="preview-item thumbnail"><img src="{{ asset("images/product-images/1002.jpg")  }}" alt=""></div></div>
                                            <div class="col-md-4 col-sm-4"><div class="preview-item thumbnail"><img src="{{ asset("images/product-images/1002.jpg")  }}" alt=""></div></div>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row margin-0">
           <div class="coming-soon">
               <h3 class="coming-soon-title"> Coming really Soon ! </h3>
               <p> We are currently building campaign features, stay with us while we complete it! </p>
           </div>
        </div>
    </div>

    <div class="container-fluid" id="mainCampaign">

    </div>
@endsection

@section('script')
{{--    <script src="{{ asset('js/moment.min.js') }}"></script>--}}
{{--    <script src="{{ asset('js/fullcalendar.js') }}"></script>--}}
{{--    <script src="{{ asset('js/schedules.js') }}"></script>--}}
@endsection
