@extends('admin.layouts.app')
@section('title', __('admin.categories.title'))
@section('subtitle', __('admin.categories.count', ['count' => $categories->count()]))

@section('actions')
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> {{ __('admin.action.new_category') }}</a>
@endsection

@section('content')
    <div class="panel">
        <div class="table-responsive">
            <table class="table table-admin">
                <thead>
                    <tr>
                        <th>{{ __('admin.col.name') }}</th>
                        <th>{{ __('admin.col.pictures') }}</th>
                        <th>{{ __('admin.col.created') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="fw-semibold">{{ $category->translated_name }}
                                @if ($category->translated_name !== $category->name)
                                    <small class="text-muted fw-normal">({{ $category->name }})</small>
                                @endif
                            </td>
                            <td>{{ $category->pictures_count }}</td>
                            <td class="text-muted small">{{ $category->created_at->translatedFormat('d M Y') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                        class="btn btn-sm btn-icon btn-light" title="{{ __('admin.action.edit') }}"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="post"
                                        data-confirm="{{ __('admin.categories.confirm_delete', ['name' => $category->translated_name, 'count' => $category->pictures_count]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-icon btn-light text-danger" title="{{ __('admin.action.delete') }}"><i
                                                class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty"><i class="bi bi-tags"></i>{{ __('admin.categories.empty') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
