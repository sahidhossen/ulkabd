<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-113371799-2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-113371799-2');
    </script>

    <title>{{ config('app.name', 'Usha Chatbot Platform') }}</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Usha's state of the art revolutionary innovation in AI technology in the chatbot industry lets you create your business chatbot in minutes. Usha AI neither requires coding nor conversational design. Usha finds customer searches through natural language processing (NLP).">
    <meta name="keywords" content="chatbot, facebook chatbot, chat bots, ai chatbot, chat robot, chatbot online, chatbot app, chatbot ai, conversational ai, facebook chat bot, Facebook bot, chatbot for website, messenger chatbot, chatbot platform, create chatbot, Usha, Usha AI, Ulka, Ulka Bangladesh, chat ai, chatbot company in bangladesh, best chatbots, best chatbot platform, best chatbot company, nlp chatbot, chatbot 2018" />
    <meta name="author" content="Ulka Bangladesh">

    <!-- Open Graph data -->
    <meta property="og:title" content="Usha | Unified Services for Human Assistance" />
    <meta property="og:description" content="Usha's state of the art revolutionary innovation in AI technology in the chatbot industry lets you create your business chatbot in minutes. Usha AI neither requires coding nor conversational design. Usha finds customer searches through natural language processing (NLP)." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://usha.ulkabd.com/" />
    <meta property="fb:app_id" content={{config('agent.facebook_protocols.app_id')}} />
    <meta property="og:image" content="https://usha.ulkabd.com/images/usha-logo-black.png" />

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@Usha_AI">
    <meta name="twitter:title" content="Usha | Unified Services for Human Assistance">
    <meta name="twitter:description" content="Usha's state of the art revolutionary innovation in AI technology in the chatbot industry lets you create your business chatbot in minutes. Usha AI neither requires coding nor conversational design. Usha finds customer searches through natural language processing (NLP).">
    <meta name="twitter:creator" content="@tmukammel">
    <meta name="twitter:image" content="https://usha.ulkabd.com/images/usha-logo-black.png">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/x-icon" href="{{ asset("favicon.ico") }}"/>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset("favicon.ico") }}"/>
    <link rel="stylesheet" href="{{ asset('css/font-awesome.css') }}">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/sa_app.css') }}"/>
    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body>
    <div class="container-fluid home" id="home">
        <nav class="navbar navbar-default">
            <div class="container">
                <div class="navbar-header">
                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <img src="{{ asset("images/usha_logo_font_page.png") }}">
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#features">About</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        @if (Auth::guest())
                            <li><a class="btn-login" href="{{ route('login') }}">Login</a></li>
                            {{--<li><a class="btn-register" href="{{ route('register') }}">Register</a></li>--}}
                        @else
                            <li><a href="{{ url('/bots') }}"> Dashboard </a></li>
                            <li>
                                <a href="{{ route('logout') }}" onclick="event.preventDefault();
                                            document.getElementById('logout-form').submit();">
                                    Logout
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </li>
                        @endif

                    </ul>

                </div>
            </div>
        </nav>

        <div class="container home-container" id="home">
            <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-7">
                    <div class="header-right-text-aria">
                        <h1>Reach the right consumer, with the right thing, at the right time!</h1>
                        <p><a href="http://m.me/UshaAI" target="_blank" class="btn btn-theme btn-lg btn-theme-red">Get Your Usha Chatbot</a></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-5">
                    <div class="usha_agent" id="usha_agent">
                        <div class="inner_agent">
                            <div class="action-view">
                                <video loop id="usha_messanger_video" autoplay>
                                    {{--<source src="somevideo.webm" type="video/webm">--}}
                                    <source src="{{ asset("images/usha-messenger-video.mp4")  }}" type="video/mp4">
                                    I'm sorry; your browser doesn't support HTML5 video in MP4 with H.264.
                                </video>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Unified Services for Human Assistance -->
    <div class="container-fluid why_use_usha" id="features">
        <div class="feature-header text-center">
            <h2> Serve Everyone at Once! </h2>
        </div>
        <div class="container our-mission-container">
            <div class="row">
                <div class="col-md-4 margin-bottom-30">
                    <div class="the-hover-effect">
                        <div class="the-a-child">
                            <h3><i class="fa fa-asl-interpreting"></i> Connect with Everyone!</h3>
                            <p>Usha connects with everyone visiting your stores and online shop. Through your messenger QR Code, everyone can subscribe at an instant and stay connected!</p>
                        </div>
                    </div>
                    
                </div>
                <div class="col-md-4 margin-bottom-30">
                    <div class="the-hover-effect">
                        <div class="the-a-child">
                        <h3><i class="fa fa-asl-interpreting"></i> Serve Smart and Instant!</h3>
                            <p>Usha can serve upto 50 people per second! Usha message responses are rich formats in the form of image, text, gif, gallery, list, button etc.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 margin-bottom-30">
                    <div class="the-hover-effect">
                        <div class="the-a-child">
                            <h3><i class="fa fa-asl-interpreting"></i> Earn Consumer Affection </h3>
                            <p>Usha understands English, Bangla, Bangla in english and local Bangla languages, and responds with what is asked. Usha never disapoints your customers!</p>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 margin-bottom-30">
                    <div class="the-hover-effect">
                        <div class="the-a-child">
                            <h3><i class="fa fa-asl-interpreting"></i> Reach Everyone </h3>
                            <p>Facebook Messenger has 2 billiion users per month. Everyone using messenger. So everyone already has you! You just need to reach out!</p>
                        </div>
                    </div>
                    
                </div>
                <div class="col-md-4 margin-bottom-30">
                    <div class="the-hover-effect">
                        <div class="the-a-child">
                            <h3><i class="fa fa-asl-interpreting"></i> Suites Your Needs</h3>
                            <p>Health, education, retail shop , hotel & restaurent, news or media, you name it. Usha is built with dynamic design in mind.</p>
                        </div>
                    </div>
                    
                </div>
                <div class="col-md-4 margin-bottom-30">
                    <div class="the-hover-effect">
                        <div class="the-a-child">
                            <h3><i class="fa fa-asl-interpreting"></i> Simplest but Best!</h3>
                            <p>Usha's innovation in chatbot design lets you build your chatbot without any conversation design. Its fast, easy and fun!</p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
    </div>

    <div class="container-fluid pricing-table" id="pricing">
        <div class="row margin-0">
            <div class="container">
                <div class="row big-offer">
                    <h2 class="big-pricing-title"> Choose the right plan </h2>
                </div>
            </div>
            <div class="table-wrapper container">
                <article class="article-pricing">
                        <table class="table-pricing">
                        <thead>
                            <tr>
                                <th class="table-pricing-header">
                                    <div class="text-container text-free">
                                        <h3 class="txt-l">Free</h3>                                      
                                    </div>     
                                </th>
                                <th class="table-pricing-header">
                                    <div class="text-container">
                                        <h3 class="txt-l">Minimal</h3>
                                        <p class="price-text">￥4,980 円<sub>/月</sub></p>       
                                    </div>     
                                </th>
                                <th class="table-pricing-header">
                                    <div class="text-container">
                                        <h3 class="txt-l">Normal</h3> 
                                        <p class="price-text">￥19,980 円<sub>/月</sub></p>      
                                    </div>     
                                </th>
                                <th class="table-pricing-header">
                                    <div class="text-container">
                                        <h3 class="txt-l">Large</h3> 
                                        <p class="price-text">￥29,980 円<sub>/月</sub></p>      
                                    </div>     
                                </th>
                                <th class="table-pricing-header right-child">
                                    <div class="text-container">
                                        <h3 class="txt-l">Enterprise</h3>    
                                        <p class="price-text">￥29,980 円<sub>/月</sub></p>   
                                    </div>     
                                </th>
                            </tr>
                            
                        </thead>
                        <tbody>
                            <tr class="table-pricing-row">
                                <td class="table-pricing-details"> 
                                    <p class="pricing-details">Dashboard</p>
                                    <p class="pricing-details">1 member account</p>
                                    <p class="pricing-details">1 chatbot</p>
                                    <p class="pricing-details">50 user days</p>
                                </td>
                                <td class="table-pricing-details"> 
                                    <p class="pricing-details">Dashboard</p>
                                    <p class="pricing-details">1 member account</p>
                                    <p class="pricing-details">1 chatbot</p>
                                    <p class="pricing-details">300 user days</p> 
                                </td>
                                <td class="table-pricing-details"> 
                                    <p class="pricing-details">Dashboard</p>
                                    <p class="pricing-details">1 member account</p>
                                    <p class="pricing-details">1 chatbot</p>
                                    <p class="pricing-details">1000 user days</p> 
                                </td>
                                <td class="table-pricing-details"> 
                                    <p class="pricing-details">Dashboard</p>
                                    <p class="pricing-details">1 member account</p>
                                    <p class="pricing-details">1 chatbot</p>
                                    <p class="pricing-details">3000 user days</p>
                                </td>
                                <td class="table-pricing-details"> 
                                    <p class="pricing-details">Dashboard</p>
                                    <p class="pricing-details">1 member account</p>
                                    <p class="pricing-details">1 chatbot</p>
                                    <p class="pricing-details">3000 user days</p>
                                    <p class="pricing-details">+</p>
                                    <p class="pricing-details">500 user days</p>
                                    <p class="bottom-paragraph">/ 4,980 円<sub>/月</sub></p>
                                </td>
                            </tr>
                            
                            <tr class="table-pricing-row-button">
                                <td class="table-pricing-details">
                                    @if (Auth::guest())
                                        <a class="table-pricing-button" href="#">Sign Up</a>
                                    @else
                                        <a class="table-pricing-button" href="{{ url('/bots') }}"> Dashboard </a>
                                    @endif
                                </td>    
                                <td class="table-pricing-details">
                                    @if (Auth::guest())
                                        <a class="table-pricing-button" href="#">Sign Up</a>
                                    @else
                                        <a class="table-pricing-button" href="{{ url('/bots') }}"> Dashboard </a>
                                    @endif
                                </td>  
                                <td class="table-pricing-details">
                                    @if (Auth::guest())
                                        <a class="table-pricing-button" href="#">Sign Up</a>
                                    @else
                                        <a class="table-pricing-button" href="{{ url('/bots') }}"> Dashboard </a>
                                    @endif
                                </td>  
                                <td class="table-pricing-details">
                                    @if (Auth::guest())
                                        <a class="table-pricing-button" href="#">Sign Up</a>
                                    @else
                                        <a class="table-pricing-button" href="{{ url('/bots') }}"> Dashboard </a>
                                    @endif
                                </td>  
                                <td class="table-pricing-details right-child">
                                    @if (Auth::guest())
                                        <a class="table-pricing-button" href="#">Sign Up</a>
                                    @else
                                        <a class="table-pricing-button" href="{{ url('/bots') }}"> Dashboard </a>
                                    @endif
                                </td>           
                            </tr>
                        </tbody>
                    </table>  
                </article>
                <div class="pricing-table-mobile">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#free_mobile" aria-controls="free_mobile" role="tab" data-toggle="tab">Free</a></li>
                        <li role="presentation"><a href="#minimal" aria-controls="minimal" role="tab" data-toggle="tab">Minimal</a></li>
                        <li role="presentation"><a href="#normal" aria-controls="normal" role="tab" data-toggle="tab">Normal</a></li>
                        <li role="presentation"><a href="#large" aria-controls="large" role="tab" data-toggle="tab">Large</a></li>
                        <li role="presentation"><a href="#enterprise" aria-controls="enterprise" role="tab" data-toggle="tab">Enterprise</a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="free_mobile">
                            <div class="card">
                                <div class="text-center pricing-mobile">
                                    <div class="text-container">
                                        <h3 class="txt-l">Free</h3>
                                    </div>  
                                </div>
                                <div class="card-body">
                                    <div class="pricing-details-mobile">
                                        <p class="pricing-details">Dashboard</p>
                                        <p class="pricing-details">1 member account</p>
                                        <p class="pricing-details">1 chatbot</p>
                                        <p class="bottom-paragraph">50 user days</p> 
                                    </div>
                                </div>
                                    <div class="mobile-price-button-container">
                                        @if (Auth::guest())
                                            <a class="pricing-button-mobile" href="#">Sign Up</a>
                                        @else
                                            <a class="pricing-button-mobile" href="{{ url('/bots') }}"> Dashboard </a>
                                        @endif
                                     </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="minimal">
                            <div class="card">
                                <div class="text-center pricing-mobile">
                                    <div class="text-container">
                                        <h3 class="txt-l">Minimal</h3>
                                        <p class="price-text">￥4,980 円<sub>/月</sub></p>       
                                    </div>  
                                </div>
                                <div class="card-body">
                                    <div class="pricing-details-mobile">
                                        <p class="pricing-details">Dashboard</p>
                                        <p class="pricing-details">1 member account</p>
                                        <p class="pricing-details">1 chatbot</p>
                                        <p class="bottom-paragraph">300 user days</p> 
                                    </div>
                                </div>
                                    <div class="mobile-price-button-container">
                                        @if (Auth::guest())
                                            <a class="pricing-button-mobile" href="#">Sign Up</a>
                                        @else
                                            <a class="pricing-button-mobile" href="{{ url('/bots') }}"> Dashboard </a>
                                        @endif
                                     </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="normal">
                            <div class="card">
                                <div class="text-center pricing-mobile">
                                    <div class="text-container">
                                        <h3 class="txt-l">Normal</h3>
                                        <p class="price-text">￥19,980 円<sub>/月</sub></p>       
                                    </div>  
                                </div>
                                <div class="card-body">
                                    <div class="pricing-details-mobile">
                                        <p class="pricing-details">Dashboard</p>
                                        <p class="pricing-details">1 member account</p>
                                        <p class="pricing-details">1 chatbot</p>
                                        <p class="bottom-paragraph">1000 user days</p>
                                    </div>
                                </div>
                                    <div class="mobile-price-button-container">
                                        @if (Auth::guest())
                                            <a class="pricing-button-mobile" href="#">Sign Up</a>
                                        @else
                                            <a class="pricing-button-mobile" href="{{ url('/bots') }}"> Dashboard </a>
                                        @endif
                                     </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="large">
                            <div class="card">
                                <div class="text-center pricing-mobile">
                                    <div class="text-container">
                                        <h3 class="txt-l">Large</h3>
                                        <p class="price-text">￥29,980 円<sub>/月</sub></p>       
                                    </div>  
                                </div>
                                <div class="card-body">
                                    <div class="pricing-details-mobile">
                                        <p class="pricing-details">Dashboard</p>
                                        <p class="pricing-details">1 member account</p>
                                        <p class="pricing-details">1 chatbot</p>
                                        <p class="bottom-paragraph">3000 user days</p>
                                    </div>
                                </div>
                                    <div class="mobile-price-button-container">
                                        @if (Auth::guest())
                                            <a class="pricing-button-mobile" href="#">Sign Up</a>
                                        @else
                                            <a class="pricing-button-mobile" href="{{ url('/bots') }}"> Dashboard </a>
                                        @endif
                                     </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="enterprise">
                            <div class="card">
                                <div class="text-center pricing-mobile">
                                    <div class="text-container">
                                        <h3 class="txt-l">Enterprise</h3>
                                        <p class="price-text">￥29,980 円<sub>/月</sub></p>       
                                    </div>  
                                </div>
                                <div class="card-body">
                                    <div class="pricing-details-mobile"> 
                                        <p class="pricing-details">Dashboard</p>
                                        <p class="pricing-details">1 member account</p>
                                        <p class="pricing-details">1 chatbot</p>
                                        <p class="pricing-details">3000 user days</p>
                                        <p class="pricing-details">+</p>
                                        <p class="pricing-details">500 user days</p>
                                        <p class="bottom-paragraph">/4,980 円<sub>/月</sub></p>
                                    </div>
                                </div>
                                    <div class="mobile-price-button-container">
                                        @if (Auth::guest())
                                            <a class="pricing-button-mobile" href="#">Sign Up</a>
                                        @else
                                            <a class="pricing-button-mobile" href="{{ url('/bots') }}"> Dashboard </a>
                                        @endif
                                     </div>
                                </div>
                            </div>
                        </div>
                    <div>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <div class="container-fluid google-map-section" id="google-map-section">
            <div class="row margin-0 map-holder">
                <div id="map"></div>
                <div class="ulka-address">
                    <div class="address_section">
                        <h3 class="title">Address</h3>
                        <p> HOUSE - 12, ROAD - 12, BLOCK - C, MIRPUR 6  <br> Dhaka 1216, Bangladesh </p>
                    </div>
                    <div class="address_section">
                        <h3 class="title"> Mobile </h3>
                        <p><a href="tel:8801726017250">+880 1726-017250 </a></p>
                    </div>
                    <div class="address_section">
                        <h3 class="title"> Email </h3>
                        <p><a href="mailto:contact@ulkabd.com "> contact@ulkabd.com </a></p>
                    </div>

                </div>
            </div>
        </div>
    <div class="container-fluid page-footer" id="page-footer">
            <div class="row margin-0">
                <div class="container">
                    <div class="row text-center" style="display: flex;">
                        <div class="col-md-6 col-sm-6 col-xs-7">
                            <div class="bottom-section">
                                <ul>
                                    <li><a href="{{ url("https://www.ulkabd.com") }}" target="_blank"><img src="{{ asset("images/ulka.png") }}" class="img-responsive"></a></li>
                                    <li><p>Copyright © 2019. All rights reserved.</p></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-5">
                            <ul class="footer-navbar">
                                <li><a href="{{ url("https://www.ulkabd.com/usha-terms") }}" target="_blank">Terms of Use</a></li>
                                <li><a href="{{ url("https://www.ulkabd.com/privacy") }}" target="_blank">Privacy Policy</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="fb-customerchat"
         page_id="1368528216563730"
         ref=""
         minimized="true">
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset("js/app.js") }}"></script>
     <script src="{{ asset('js/owl.carousel.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.19.1/TimelineMax.min.js"></script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyALRiARd71DVN53agUvDRgaqv1GC5uR0lg" type="text/javascript"></script>
    <script src="{{ asset('js/landing_scirpt.js') }}"></script>
<script>
    $(document).ready(function(){
        jQuery(".owl-carousel").owlCarousel({
            items: 4,
            responsiveClass:true,
            autoplay: true,
            autoPlaySpeed: 5000,
            autoPlayTimeout: 5000,
            autoplayHoverPause: true,
            loop: true,
            center: true,
            margin:20,
            responsive:{
                0:{
                    items:1,
                    nav:false
                },
                769:{
                    items:3,
                    nav:false,
                    loop:true,
                    autoplay: true,
                    center: true,
                },
                992:{
                    items:4,
                    nav:true,
                    loop:true,
                    autoplay: true,
                    center: true,
                }
            }
        });


        function initMap() {
            // Styles a map in night mode.
            var myLatLng = {lat: 23.814904, lng: 90.36524699999995};
            var map = new google.maps.Map(document.getElementById('map'), {
                center: myLatLng,
                zoom: 15,
                scrollwheel: false,
                styles: [
                    {elementType: 'geometry', stylers: [{color: '#242f3e'}]},
                    {elementType: 'labels.text.stroke', stylers: [{color: '#242f3e'}]},
                    {elementType: 'labels.text.fill', stylers: [{color: '#746855'}]},
                    {
                        featureType: 'administrative.locality',
                        elementType: 'labels.text.fill',
                        stylers: [{color: '#d59563'}]
                    },
                    {
                        featureType: 'poi',
                        elementType: 'labels.text.fill',
                        stylers: [{color: '#d59563'}]
                    },
                    {
                        featureType: 'poi.park',
                        elementType: 'geometry',
                        stylers: [{color: '#263c3f'}]
                    },
                    {
                        featureType: 'poi.park',
                        elementType: 'labels.text.fill',
                        stylers: [{color: '#6b9a76'}]
                    },
                    {
                        featureType: 'road',
                        elementType: 'geometry',
                        stylers: [{color: '#38414e'}]
                    },
                    {
                        featureType: 'road',
                        elementType: 'geometry.stroke',
                        stylers: [{color: '#212a37'}]
                    },
                    {
                        featureType: 'road',
                        elementType: 'labels.text.fill',
                        stylers: [{color: '#1BB39A'}]
                    },
                    {
                        featureType: 'road.highway',
                        elementType: 'geometry',
                        stylers: [{color: '#746855'}]
                    },
                    {
                        featureType: 'road.highway',
                        elementType: 'geometry.stroke',
                        stylers: [{color: '#1f2835'}]
                    },
                    {
                        featureType: 'road.highway',
                        elementType: 'labels.text.fill',
                        stylers: [{color: '#f3d19c'}]
                    },
                    {
                        featureType: 'transit',
                        elementType: 'geometry',
                        stylers: [{color: '#2f3948'}]
                    },
                    {
                        featureType: 'transit.station',
                        elementType: 'labels.text.fill',
                        stylers: [{color: '#d59563'}]
                    },
                    {
                        featureType: 'water',
                        elementType: 'geometry',
                        stylers: [{color: '#17263c'}]
                    },
                    {
                        featureType: 'water',
                        elementType: 'labels.text.fill',
                        stylers: [{color: '#515c6d'}]
                    },
                    {
                        featureType: 'water',
                        elementType: 'labels.text.stroke',
                        stylers: [{color: '#17263c'}]
                    }
                ]
            });

            var marker = new google.maps.Marker({
                position: myLatLng,
                map: map,
                title: 'Ulka Bangladesh Office'
            });
        }
        initMap();

        /*
        * Menu item click
        */
        $('.navbar-nav').on('click', 'a[href^="#"]', function(event) {
            var target = $( $(this).attr('href') );
            if( target.length ) {
                event.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top
                }, 1000);
            }
        });

//        var video = document.getElementById('usha_admin_video');
//        video.controls = false;
//        video.play();

        var ushaMessangerVideo = document.getElementById("usha_messanger_video");
        if (ushaMessangerVideo) {
            ushaMessangerVideo.controls = false;
            ushaMessangerVideo.play();
        }

    });
</script>
<script>
    let appId = <?php echo json_encode($appId) ?>;
    let version = <?php echo json_encode($fb_api_version) ?>;
    
    if (appId && version) {
//        console.debug("App id: ", appId);
        window.fbAsyncInit = function() {
            FB.init({
                appId: appId,
                xfbml: true,
                version: version
            });

            FB.Event.subscribe('customerchat.load', function() {
                console.debug("customer chat plugin loaded");
                FB.CustomerChat.show(true);
            });
        };

        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) { return; }
            js = d.createElement(s); js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    }
</script>
</body>
</html>
