<?php
//set headers to NOT cache a page
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-113371799-2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-113371799-2');
    </script>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> {{ config('app.name', 'Laravel') }}  </title>

    <!-- Styles -->
    <link href="{{ asset('css/webview.css') }}" rel="stylesheet">
    {{--Font awesome--}}
    <link rel="stylesheet" href="{{ asset('css/font-awesome.css') }}">
    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body>

@yield('content')

<!-- Scripts -->
<script>

</script>
<script>
    let fbThreadContext = null;
//
//    window.extAsyncInit = function() {
//
//        MessengerExtensions.getContext('120759628496384',
//            function success(thread_context){
//                fbThreadContext = thread_context;
//            },
//            function error(err){
//                // error
//            }
//        );
//
//    };
</script>
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/webview.js') }}"></script>
@yield('script')
</body>
</html>