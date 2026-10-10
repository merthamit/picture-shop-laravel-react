@extends('admin.layouts.app')
@section('title', 'Orders')
@section('subtitle', $orders->total() . ' orders')

@section('content')
    <div class="panel">
        <div class="panel-header">
            <form class="filters" method="GET">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                    placeholder="Search buyer or picture">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (['completed' => 'Completed', 'pending' => 'Processing', 'failed' => 'Failed'] as $value => $label)
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
                        <th>#</th>
                        <th>Picture</th>
                        <th>Buyer</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Date</th>
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
                            <td class="text-muted small">{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td class="text-end">
                                <form action="{{ route('admin.orders.destroy', $order) }}" method="post"
                                    data-confirm="Delete order #{{ $order->id }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-icon btn-light text-danger" title="Delete"><i
                                            class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty"><i class="bi bi-bag"></i>No orders found</div>
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
