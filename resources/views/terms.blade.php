<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Usha is an AI Program. It helps you find your goods from f-commerce shops.">
    {{--<meta name="keywords" content="Chatbot, facebook chatbot, Facebook bot, bot, create chatbot" />--}}
    <meta name="author" content="Ulka BD">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ulka BD Terms') }}</title>
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
<div class="container-fluid home" id="privacy-home">
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
                    {{--{{ config('app.name', 'UlkaBot') }}--}}
                    <img src="{{ asset("images/usha_logo_font_page.png") }}">
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
            </div>
        </div>
    </nav>

    <div class="container special-container">
        <br>
        <h3 class="title text-center"> USHA Official Terms and Services </h3>
        <div class="privacy-container">
            <br>
            <br>
            <h4><strong>Effective date: 20th of May 2017</strong></h4>
            <br>

            <br>
        </div>
    </div>
</div>
<div class="container-fluid page-footer" id="page-footer">
    <div class="row margin-0">
        <div class="container">
            <div class="col-lg-12 col-md-12 contact-from-section text-center">
                <form class="navbar-form" role="search">
                    <div class="form-group">
                        <label class="subscribe-text">Subscribe For More : </label>
                        <input type="text" class="form-control" placeholder="Enter Email Address">
                    </div>
                    <button type="submit" class="btn btn-theme btn-default">SUBSCRIBE</button>
                </form>
            </div>

            <div class="row text-center">
                <div class="col-md-4 col-sm-6 col-xs-7">
                    <div class="bottom-section">
                        <ul>
                            <li><img src="{{ asset("images/ulka.png") }}" class="img-responsive"></li>
                            <li><p>All Right Reserved 2017</p></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-8 col-sm-6 col-xs-5">
                    <ul class="footer-navbar">
                        <li><a href="{{ url("/terms") }}">Terms</a></li>
                        <li><a href="{{ url("/privacy") }}">Privacy</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Scripts -->
<script src="{{ asset('js/jquery.min.js') }}"></script>

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/owl.carousel.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.19.1/TimelineMax.min.js"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyALRiARd71DVN53agUvDRgaqv1GC5uR0lg" type="text/javascript"></script>
<script src="{{ asset('js/landing_scirpt.js') }}"></script>
</body>
</html>
