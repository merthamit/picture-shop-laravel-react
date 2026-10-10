@extends('admin.layouts.app')
@section('title', 'Edit category')

@section('content')
    @include('admin.categories.form', [
        'action' => route('admin.categories.update', $category),
        'method' => 'PUT',
    ])
@endsection
