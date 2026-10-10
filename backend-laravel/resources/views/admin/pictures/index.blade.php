@extends('admin.layouts.app')
@section('title', 'Pictures')
@section('subtitle', $pictures->total() . ' pictures')

@section('content')
    <div class="panel">
        <div class="panel-header">
            <form class="filters" method="GET">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                    placeholder="Search title">
                <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Any status</option>
                    @foreach (['live' => 'Live', 'hidden' => 'Hidden', 'deleted' => 'Removed by seller'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-light"><i class="bi bi-search"></i></button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-admin">
                <thead>
                    <tr>
                        <th>Picture</th>
                        <th>Seller</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Sales</th>
                        <th>Storage</th>
                        <th>Status</th>
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
                            <td>{{ $picture->category?->name }}</td>
                            <td class="fw-semibold">${{ $picture->price }}</td>
                            <td>{{ $picture->orders_count }}</td>
                            <td>
                                @if ($picture->disk === 'cloudinary')
                                    <span class="pill pill-muted"><i class="bi bi-cloud"></i>Cloudinary</span>
                                @else
                                    <span class="pill pill-muted">Local</span>
                                @endif
                            </td>
                            <td>
                                @if ($picture->trashed())
                                    <span class="pill pill-danger">Removed</span>
                                @elseif ($picture->status)
                                    <span class="pill pill-success">Live</span>
                                @else
                                    <span class="pill pill-warning">Hidden</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @unless ($picture->trashed())
                                    @if ($picture->status)
                                        <a href="{{ route('admin.pictures.edit', ['picture' => $picture, 'status' => 0]) }}"
                                            class="btn btn-sm btn-light" title="Hide from shop"><i class="bi bi-eye-slash"></i>
                                            Hide</a>
                                    @else
                                        <a href="{{ route('admin.pictures.edit', ['picture' => $picture, 'status' => 1]) }}"
                                            class="btn btn-sm btn-light text-success" title="Publish"><i
                                                class="bi bi-eye"></i> Publish</a>
                                    @endif
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty"><i class="bi bi-images"></i>No pictures found</div>
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
