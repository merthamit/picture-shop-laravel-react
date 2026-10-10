@extends('admin.layouts.app')
@section('title', __('admin.pictures.title'))
@section('subtitle', __('admin.pictures.count', ['count' => $pictures->total()]))

@section('content')
    <div class="panel">
        <div class="panel-header">
            <form class="filters" method="GET">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                    placeholder="{{ __('admin.filter.search_title') }}">
                <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('admin.filter.all_categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->translated_name }}
                        </option>
                    @endforeach
                </select>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('admin.filter.any_status') }}</option>
                    @foreach (['live' => 'live', 'hidden' => 'hidden', 'deleted' => 'removed_by_seller'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ __('admin.status.' . $label) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-light"><i class="bi bi-search"></i></button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-admin">
                <thead>
                    <tr>
                        <th>{{ __('admin.col.picture') }}</th>
                        <th>{{ __('admin.col.seller') }}</th>
                        <th>{{ __('admin.col.category') }}</th>
                        <th>{{ __('admin.col.price') }}</th>
                        <th>{{ __('admin.col.sales') }}</th>
                        <th>{{ __('admin.col.storage') }}</th>
                        <th>{{ __('admin.col.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pictures as $picture)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ $picture->image_path }}" target="_blank"><img
                                            src="{{ $picture->thumbnail_path }}" alt="" class="thumb"></a>
                                    <div>
                                        <div class="fw-semibold">{{ $picture->title }}</div>
                                        <small class="text-muted text-uppercase">{{ $picture->ext }}
                                            @if ($picture->width)
                                                · {{ $picture->width }}×{{ $picture->height }}
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $picture->user?->name }}</td>
                            <td>{{ $picture->category?->translated_name }}</td>
                            <td class="fw-semibold">${{ $picture->price }}</td>
                            <td>{{ $picture->orders_count }}</td>
                            <td>
                                @if ($picture->disk === 'cloudinary')
                                    <span class="pill pill-muted"><i class="bi bi-cloud"></i>Cloudinary</span>
                                @else
                                    <span class="pill pill-muted">{{ __('admin.pictures.local') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($picture->trashed())
                                    <span class="pill pill-danger">{{ __('admin.status.removed') }}</span>
                                @elseif ($picture->status)
                                    <span class="pill pill-success">{{ __('admin.status.live') }}</span>
                                @else
                                    <span class="pill pill-warning">{{ __('admin.status.hidden') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @unless ($picture->trashed())
                                    @if ($picture->status)
                                        <a href="{{ route('admin.pictures.edit', ['picture' => $picture, 'status' => 0]) }}"
                                            class="btn btn-sm btn-light"><i class="bi bi-eye-slash"></i> {{ __('admin.action.hide') }}</a>
                                    @else
                                        <a href="{{ route('admin.pictures.edit', ['picture' => $picture, 'status' => 1]) }}"
                                            class="btn btn-sm btn-light text-success"><i class="bi bi-eye"></i> {{ __('admin.action.publish') }}</a>
                                    @endif
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty"><i class="bi bi-images"></i>{{ __('admin.pictures.empty') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pictures->hasPages())
            <div class="panel-body border-top">{{ $pictures->links() }}</div>
        @endif
    </div>
@endsection
