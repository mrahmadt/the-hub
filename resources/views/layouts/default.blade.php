<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="{{asset('css/app.css')}}">
<title>{{__('messages.AppName')}} - @yield('title')</title>
<meta name="apple-mobile-web-app-title" content="{{__('messages.AppName')}}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="application-name" content="{{__('messages.AppName')}}">
<meta name="msapplication-starturl" content="{{ url('/myapps') }}">
<link rel="apple-touch-icon" sizes="57x57" href="{{asset('/mobile/apple-icon-57x57.png')}}">
<link rel="apple-touch-icon" sizes="60x60" href="{{asset('/mobile/apple-icon-60x60.png')}}">
<link rel="apple-touch-icon" sizes="72x72" href="{{asset('/mobile/apple-icon-72x72.png')}}">
<link rel="apple-touch-icon" sizes="76x76" href="{{asset('/mobile/apple-icon-76x76.png')}}">
<link rel="apple-touch-icon" sizes="114x114" href="{{asset('/mobile/apple-icon-114x114.png')}}">
<link rel="apple-touch-icon" sizes="120x120" href="{{asset('/mobile/apple-icon-120x120.png')}}">
<link rel="apple-touch-icon" sizes="144x144" href="{{asset('/mobile/apple-icon-144x144.png')}}">
<link rel="apple-touch-icon" sizes="152x152" href="{{asset('/mobile/apple-icon-152x152.png')}}">
<link rel="apple-touch-icon" sizes="180x180" href="{{asset('/mobile/apple-icon-180x180.png')}}">
<link rel="apple-touch-startup-image" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)" href="{{asset('/mobile/apple-launch-1242x2688.png')}}"><!-- iPhone Xs Max (1242px x 2688px) --> 
<link rel="apple-touch-startup-image" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)" href="{{asset('/mobile/apple-launch-828x1792.png')}}"> <!-- iPhone Xr (828px x 1792px) --> 
<link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)" href="{{asset('/mobile/apple-launch-1125x2436.png')}}">  <!-- iPhone X, Xs (1125px x 2436px) --> 
<link rel="apple-touch-startup-image" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3)" href="{{asset('/mobile/apple-launch-1242x2208.png')}}"> <!-- iPhone 8 Plus, 7 Plus, 6s Plus, 6 Plus (1242px x 2208px) -->
<link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)" href="{{asset('/mobile/apple-launch-750x1334.png')}}"> <!-- iPhone 8, 7, 6s, 6 (750px x 1334px) -->
<link rel="apple-touch-startup-image" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2)" href="{{asset('/mobile/apple-launch-2048x2732.png')}}">  <!-- iPad Pro 12.9" (2048px x 2732px) --> 
<link rel="apple-touch-startup-image" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2)" href="{{asset('/mobile/apple-launch-1668x2388.png')}}"> <!-- iPad Pro 11” (1668px x 2388px) --> 
<link rel="apple-touch-startup-image" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2)" href="{{asset('/mobile/apple-launch-1668x2224.png')}}"> <!-- iPad Pro 10.5" (1668px x 2224px) --> 
<link rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2)" href="{{asset('/mobile/apple-launch-1536x2048.png')}}"> <!-- iPad Mini, Air (1536px x 2048px) --> 
<link rel="icon" type="image/png" sizes="192x192"  href="{{asset('/mobile/android-icon-192x192.png')}}">
<link rel="icon" type="image/png" sizes="32x32" href="{{asset('/mobile/favicon-32x32.png')}}">
<link rel="icon" type="image/png" sizes="96x96" href="{{asset('/mobile/favicon-96x96.png')}}">
<link rel="icon" type="image/png" sizes="16x16" href="{{asset('/mobile/favicon-16x16.png')}}">
<link rel="icon" type="image/x-icon" href="{{asset('/favicon.ico')}}">
<link rel="manifest" href="{{asset('/manifest.json')}}">
<meta name="theme-color" content="#ffffff">

</head>
<body>

<!-- 
CUSTOM : navigation bar https://getbootstrap.com/docs/4.5/components/navbar/#color-schemes & https://getbootstrap.com/docs/4.5/utilities/colors/
navbar-light or navbar-dark (links and text color)
bg-primary or bg-secondary or bg-success or bg-danger or bg-warning or bg-info or bg-light bg-dark
-->
<header class="navbar navbar-expand  flex-column flex-md-row bd-navbar navbar-light" >
    <a class="navbar-brand" href="{{ url('/myapps') }}"><img src="{{asset('img/logo.svg')}}" width="30" height="30" alt="" loading="lazy"> {{ __('messages.AppName') }}</a> 
    <div class="navbar-nav-scroll">
    <ul class="navbar-nav bd-navbar-nav flex-row">
      <li class="nav-item">
        <a class="nav-link {{ (request()->is('myapps')) ? 'active' : '' }}" href="{{ url('/myapps') }}">{{ __('messages.My Apps') }}</a>
      </li>
      @foreach($tabs as $tab)
      <li class="nav-item">
        @if ($tab->tabtype->action === 0)
        <a class="nav-link {{ (request()->is('categories')) ? 'active' : '' }}" href="{{ url('/categories') }}">{{$tab->name}}</a>
        @elseif ($tab->tabtype->action == 1)
        <a class="nav-link {{ (request()->is('tab/page/'.$tab->id)) ? 'active' : '' }}" href="{{ url('/tab/page/'.$tab->id) }}">{{$tab->name}}</a>
        @elseif ($tab->tabtype->action == 2 && $tab->tabtype->url_iframe == 0)
        <a class="nav-link" href="{{ $tab->url }}">{{$tab->name}}</a>
        @elseif ($tab->tabtype->action == 2 && $tab->tabtype->url_iframe == 1)
        <a class="nav-link {{ (request()->is('tab/iframe/'.$tab->id)) ? 'active' : '' }}" href="{{ url('/tab/iframe/'.$tab->id) }}">{{$tab->name}}</a>
        @elseif ($tab->tabtype->action == 3)
        <a class="nav-link {{ (request()->is('categories/'.$tab->category_id)) ? 'active' : '' }}" href="{{ url('/categories/'.$tab->category_id) }}">{{$tab->name}}</a>
        @endif
      </li>
      @endforeach
      
      @if (request()->is('myapps'))
      <li class="nav-item d-lg-none">
        <a class="nav-link show-unpin-apps" href="#">Edit my apps</a>
      </li>
      @endif
      <li class="nav-item d-lg-none">
        <a class="nav-link" href="{{ url('/logout') }}">Logout</a>
      </li>

    </ul>
  </div>

  <ul class="navbar-nav flex-row ml-md-auto d-none d-md-flex">
    <li class="nav-item dropdown">
      <a class="nav-item nav-link dropdown-toggle mr-md-2" href="#" id="bd-versions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <img src="{{$user->avatar ?? asset('/img/avatar1.png')}}" class="rounded-circle z-depth-0" alt="avatar image" height="35">
      </a>
      <div class="dropdown-menu dropdown-menu-right" aria-labelledby="bd-versions">
      <a class="dropdown-item" href="{{ url('/myapps') }}">{{__('messages.Hello')}} {{$user->name}}</a>
    @if (request()->is('myapps'))
        <a class="dropdown-item show-unpin-apps" href="#">Edit my apps</a>
    @endif
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ url('/logout') }}"><svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-door-closed-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M4 1a1 1 0 0 0-1 1v13H1.5a.5.5 0 0 0 0 1h13a.5.5 0 0 0 0-1H13V2a1 1 0 0 0-1-1H4zm2 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
</svg> Logout</a>
      </div>
    </li>
  </ul>
</header>

@section('sidebar')

@show
<div class="container-fluid mt-3 {{$content_class??''}}">
@if (isset($settings['ui.banner.top']) && $settings['ui.banner.top']['value']!='')
<div class="img-auto-fluid">
{!!$settings['ui.banner.top']['value']!!}
</div>
@endif  
@yield('content')
</div>


<script src="{{asset('js/app.js')}}"></script>

<div class="modal fade" id="app-info-modal" tabindex="-1" role="dialog" aria-labelledby="app-infoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="app-infoLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="app-info-body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
</body>
</html>
