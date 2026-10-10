@extends('admin.layouts.app')
@section('title', __('admin.categories.new'))

@section('content')
    @include('admin.categories.form', ['action' => route('admin.categories.store')])
@endsection
