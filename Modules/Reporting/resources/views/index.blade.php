@extends('reporting::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('reporting.name') !!}</p>
@endsection
