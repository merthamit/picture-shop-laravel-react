@extends('admin.layouts.app')
@section('title', __('admin.reviews.title'))
@section('subtitle', __('admin.reviews.subtitle'))

@section('content')
    <div class="panel">
        <div class="panel-header">
            <div class="btn-group btn-group-sm">
                @foreach (['' => 'filter.all', 'visible' => 'status.visible', 'hidden' => 'status.hidden'] as $value => $label)
                    <a href="{{ route('admin.reviews.index', array_filter(['status' => $value])) }}"
                        class="btn {{ request('status', '') === $value ? 'btn-primary' : 'btn-light' }}">{{ __('admin.' . $label) }}</a>
                @endforeach
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-admin">
                <thead>
                    <tr>
                        <th>{{ __('admin.col.picture') }}</th>
                        <th>{{ __('admin.col.review') }}</th>
                        <th>{{ __('admin.col.by') }}</th>
                        <th>{{ __('admin.col.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td>
                                <img src="{{ $review->picture?->thumbnail_path }}" alt="" class="thumb">
                            </td>
                            <td style="max-width: 420px">
                                <div class="stars small mb-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </div>
                                <div>{{ $review->comment }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $review->user?->name }}</div>
                                <small class="text-muted">{{ $review->created_at }}</small>
                            </td>
                            <td>
                                @if ($review->approved)
                                    <span class="pill pill-success">{{ __('admin.status.visible') }}</span>
                                @else
                                    <span class="pill pill-muted">{{ __('admin.status.hidden') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    @if ($review->approved)
                                        <a href="{{ route('admin.reviews.edit', ['review' => $review, 'status' => 0]) }}"
                                            class="btn btn-sm btn-light"><i class="bi bi-eye-slash"></i>
                                            {{ __('admin.action.hide') }}</a>
                                    @else
                                        <a href="{{ route('admin.reviews.edit', ['review' => $review, 'status' => 1]) }}"
                                            class="btn btn-sm btn-primary"><i class="bi bi-eye"></i>
                                            {{ __('admin.action.publish') }}</a>
                                    @endif
                                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="post"
                                        data-confirm="{{ __('admin.reviews.confirm_delete') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-icon btn-light text-danger"
                                            title="{{ __('admin.action.delete') }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty"><i class="bi bi-star"></i>{{ __('admin.reviews.empty') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reviews->hasPages())
            <div class="panel-body border-top">{{ $reviews->links() }}</div>
        @endif
    </div>
@endsection
