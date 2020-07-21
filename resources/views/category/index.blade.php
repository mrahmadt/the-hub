@extends('layouts.default')

@section('title', 'Apps')

@section('content')
  <div class="row flex-xl-nowrap">


<div class="col-12 col-md-2 col-xl-2 bd-sidebar">



<div class="d-md-none card text-left p-0 mb-0">
<div class="card-body p-0 m-0">
<button style="border: 1px solid;" class="card-text btn btn-block btn-link bd-search-docs-toggle d-md-none text-left" type="button" data-toggle="collapse" data-target="#bd-sidebar-list" aria-controls="bd-sidebar-list" aria-expanded="false" aria-label="Toggle categories navigation"><svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 30 30" width="30" height="30" focusable="false"><title>Menu</title><path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-miterlimit="10" d="M4 7h22M4 15h22M4 23h22"/></svg>
All Apps
</button>
</div>
</div>

<div class="collapse-sm"  id="bd-sidebar-list">
<ul class="nav">
  <li class="{{ (request()->is('categories')) ? 'active' : '' }}"><a href="{{ url('/categories') }}">{{ __('messages.All Apps') }}</a></li>
  @foreach($categories as $category)
  <li class="{{ (request()->is('categories/'.$category->id)) ? 'active' : '' }}"><a href="{{ url('categories/'.$category->id) }}">{{$category->name}}</a></li>
  @endforeach
</ul>
</div>
</div>

<div class="col-12 col-md-10 col-xl-10" id="category-apps-list">
<div class="row">
@foreach($applications as $application)
    <div class="col-6 col-sm-6 col-md-3 col-lg-2 col-xl-2 pb-3 text-center" id="AppID{{$application->id}}">
      <div class="card">
        <a href="{{ $application->url }}" class="app-link {{ ($application->isNewPageForIframe) ? 'NewPageForIframe' : '' }}" {{ ($application->isNewPage) ? 'target=_new' : '' }} isFeatured> 
          <img src="{{ $application->icon ?? asset('/img/appicon.png')}}" class="rounded img-fluid app-icon d-block mx-auto">
          <div class="text-truncate app-name-{{$application->id}}">{{ $application->name }}</div>
        </a>
  <div>
        @if(!isset($pinned_applications[$application->id]))
        <a href="#pin" data-id="{{$application->id}}" data-url="{{url('/application/pin/'.$application->id)}}" class="btn-pin-app" title="{{ __('messages.Pin to my apps') }}">
        <svg width="2em" height="2em" viewBox="0 0 16 16" class="bi bi-file-plus-fill pin-icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M12 1H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zM8.5 6a.5.5 0 0 0-1 0v1.5H6a.5.5 0 0 0 0 1h1.5V10a.5.5 0 0 0 1 0V8.5H10a.5.5 0 0 0 0-1H8.5V6z"/>
        </svg>
        </a>
        @else
        <svg width="2em" height="2em" viewBox="0 0 16 16" class="bi bi-file-plus-fill pin-icon-gray" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M12 1H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zM8.5 6a.5.5 0 0 0-1 0v1.5H6a.5.5 0 0 0 0 1h1.5V10a.5.5 0 0 0 1 0V8.5H10a.5.5 0 0 0 0-1H8.5V6z"/>
        </svg>
        @endif
      <a href="#info" data-id="{{$application->id}}" data-url="{{url('/application/info/'.$application->id)}}" class="btn-info-app"  title="{{ __('messages.App info') }}">
        <svg width="1.7em" height="1.7em" viewBox="0 0 16 16" class="bi bi-info-square-fill info-app-icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm8.93 4.588l-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM8 5.5a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
        </svg>
        </a>
</div>
      </div>
    </div>
@endforeach
  </div>
</div>

</div>


@endsection
