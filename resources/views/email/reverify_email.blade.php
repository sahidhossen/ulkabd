<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> {{__('pages.emailVerify.email.title')}} </title>
</head>
<body>

<p style="text-align: center;"><img class="img-responsive" src="{{ asset("images/usha-login-logo.png")  }}" alt="ulka" /></p>

<h1 style="text-align: center;">
    {{__('pages.emailVerify.email.body.heading')}}
    <a
            style="
                border: 1px solid #D0021B;
                -webkit-border-radius:5px ;
                -moz-border-radius:5px ;
                border-radius: 5px;
                display: inline-block;
                text-align: center;
                padding: 5px 12px;
            "
            href="{{url('/reverify_email/'.$email_token)}}"> {{__('pages.emailVerify.email.body.link')} </a>
</h1>


</body>
</html>
