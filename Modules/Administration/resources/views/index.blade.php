@extends('administration::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('administration.name') !!}</p>
@endsection
