@extends('layouts.default', ['content_class'=>'p-0'])

@section('title', $name)
@section('content')
<iframe src="{{$url}}" title="{{$name}}" frameborder="0" style="width: 100%; height: 100vh;" height="100%" width="100%"></iframe>
@endsection
