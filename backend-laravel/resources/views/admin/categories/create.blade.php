@extends('admin.layouts.app')
@section('title', 'New category')

@section('content')
    @include('admin.categories.form', ['action' => route('admin.categories.store')])
@endsection
