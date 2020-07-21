
@extends('layouts.default')

@section('title', 'My Apps')

@section('content')
<!--
Custom: Change alert color https://getbootstrap.com/docs/4.0/components/alerts/
-->
@if (isset($settings['users.announcement']['uid']))
<div class="d-none alert alert-info alert-dismissible fade show img-auto-fluid" id="{{$settings['users.announcement']['uid'] ?? ''}}" role="alert">
{!!$settings['users.announcement']['value']!!}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif
<div class="row">
@foreach($applications as $application)
    <div class="col-6 col-sm-6 col-md-3 col-lg-2 col-xl-2 pb-3 text-center" id="AppID{{$application->id}}">
      <div class="card">
        <a href="#"  data-url="{{url('/application/unpin/'.$application->id)}}"  data-id="{{$application->id}}" class="d-none btn-unpin-app" title="{{ __('messages.Unpin app') }}">
        <svg width="1.5em" height="1.5em" viewBox="0 0 16 16" class="bi bi-x-circle-fill unpin-icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.146-3.146a.5.5 0 0 0-.708-.708L8 7.293 4.854 4.146a.5.5 0 1 0-.708.708L7.293 8l-3.147 3.146a.5.5 0 0 0 .708.708L8 8.707l3.146 3.147a.5.5 0 0 0 .708-.708L8.707 8l3.147-3.146z"/>
        </svg>
        </a>
        <a href="{{ $application->url }}" class="app-link {{ ($application->isNewPageForIframe) ? 'NewPageForIframe' : '' }}" {{ ($application->isNewPage) ? 'target=_new' : '' }} isFeatured> 
          <img src="{{ $application->icon ?? asset('/img/appicon.png')}}" class="rounded img-fluid app-icon d-block mx-auto">
          <div class="text-truncate app-name-{{$application->id}}">{{ $application->name}}</div>
        </a>
      </div>
    </div>
@endforeach
</div>
@endsection
