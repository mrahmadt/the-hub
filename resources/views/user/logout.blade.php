<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="{{asset('css/app.css')}}">
<title>{{__('messages.AppName')}} - Logout</title>
</head>
<body class="">
<div class="container" style="padding-top:10em;">

<div class="card"><div class="card-body img-auto-fluid text-center">
    <img src="{{asset('img/logo.svg')}}" width="100" height="100" alt="" loading="lazy">
    <p>
    <h1>Good Bye</h1><a class="btn btn-primary" href="{{ url('/') }}">Login</a></div></div>
    </p>

</div>
<script src="{{asset('js/app.js')}}"></script>
</body>
</html>
