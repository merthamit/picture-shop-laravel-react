@extends('admin.layouts.app')
@section('title', __('admin.categories.edit'))

@section('content')
    @include('admin.categories.form', [
        'action' => route('admin.categories.update', $category),
        'method' => 'PUT',
    ])
@endsection
