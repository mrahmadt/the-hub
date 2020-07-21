@extends('layouts.default')

@section('title', $page->title)
@section('content')
<div class="card p-1"><div class="card-body img-auto-fluid">{!!$page->body!!}</div></div>
@endsection
