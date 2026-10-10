@extends('admin.layouts.app')
@section('title', __('admin.orders.title'))
@section('subtitle', __('admin.orders.count', ['count' => $orders->total()]))

@section('content')
    <div class="panel">
        <div class="panel-header">
            <form class="filters" method="GET">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                    placeholder="{{ __('admin.filter.search_orders') }}">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('admin.filter.all_statuses') }}</option>
                    @foreach (['completed', 'pending', 'failed'] as $value)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ __('admin.status.' . $value) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-light"><i class="bi bi-search"></i></button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-admin">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('admin.col.picture') }}</th>
                        <th>{{ __('admin.col.buyer') }}</th>
                        <th>{{ __('admin.col.price') }}</th>
                        <th>{{ __('admin.col.status') }}</th>
                        <th>{{ __('admin.col.date') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="text-muted">{{ $order->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $order->picture->thumbnail_path }}" alt="" class="thumb">
                                    <span class="fw-semibold">{{ $order->picture->title }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $order->user->name }}</div>
                                <small class="text-muted">{{ $order->user->email }}</small>
                            </td>
                            <td class="fw-semibold">${{ $order->picture->price }}</td>
                            <td>@include('admin.partials.order-status', ['status' => $order->status])</td>
                            <td class="text-muted small">{{ $order->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td class="text-end">
                                <form action="{{ route('admin.orders.destroy', $order) }}" method="post"
                                    data-confirm="{{ __('admin.orders.confirm_delete', ['id' => $order->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-icon btn-light text-danger" title="{{ __('admin.action.delete') }}"><i
                                            class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty"><i class="bi bi-bag"></i>{{ __('admin.orders.empty') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="panel-body border-top">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
