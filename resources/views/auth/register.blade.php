@extends('layouts.app')

@section('title', 'Sign up')

@section('main_class', 'py-2 py-md-3 auth-page-main')

@section('content')
    @include('auth.partials.auth-split', ['initialTab' => 'register'])
@endsection
