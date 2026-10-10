@extends('admin.layouts.app')
@section('title', 'Categories')
@section('subtitle', $categories->count() . ' categories')

@section('actions')
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New
        category</a>
@endsection

@section('content')
    <div class="panel">
        <div class="table-responsive">
            <table class="table table-admin">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Pictures</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td>{{ $category->pictures_count }}</td>
                            <td class="text-muted small">{{ $category->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                        class="btn btn-sm btn-icon btn-light" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="post"
                                        data-confirm="Delete {{ $category->name }}? Its {{ $category->pictures_count }} picture(s) will be deleted too.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-icon btn-light text-danger" title="Delete"><i
                                                class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty"><i class="bi bi-tags"></i>No categories yet</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
