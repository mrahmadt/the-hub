@extends('layouts.default')

@section('title', $name)
@section('content')
<div class="card p-1"><div class="card-body img-auto-fluid">{!!$body!!}</div></div>
@endsection
