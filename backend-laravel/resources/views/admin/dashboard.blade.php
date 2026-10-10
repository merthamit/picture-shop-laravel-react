@extends('admin.layouts.app')
@section('title', __('admin.nav.dashboard'))
@section('subtitle', __('admin.dashboard.subtitle'))

@section('content')
    @php
        $cards = [
            ['label' => __('admin.dashboard.revenue'), 'value' => '$' . number_format($stats['revenue']), 'icon' => 'bi-cash-stack'],
            ['label' => __('admin.dashboard.orders'), 'value' => $stats['orders'], 'icon' => 'bi-bag-check'],
            ['label' => __('admin.dashboard.users'), 'value' => $stats['users'], 'icon' => 'bi-people'],
            ['label' => __('admin.dashboard.live_pictures'), 'value' => $stats['pictures'], 'icon' => 'bi-images'],
        ];
        $max = max(1, $chart->max('total'));
    @endphp

    <div class="row g-3 mb-4">
        @foreach ($cards as $card)
            <div class="col-6 col-xl-3">
                <div class="panel stat">
                    <div>
                        <div class="value">{{ $card['value'] }}</div>
                        <div class="label">{{ $card['label'] }}</div>
                    </div>
                    <span class="icon"><i class="bi {{ $card['icon'] }}"></i></span>
                </div>
            </div>
        @endforeach
    </div>

    @if ($stats['hidden_reviews'])
        <div class="alert alert-light border d-flex align-items-center gap-2">
            <i class="bi bi-eye-slash"></i>
            {{ __('admin.dashboard.hidden_reviews', ['count' => $stats['hidden_reviews']]) }}
            <a href="{{ route('admin.reviews.index', ['status' => 'hidden']) }}" class="ms-auto fw-semibold">{{ __('admin.dashboard.manage') }}</a>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="panel h-100">
                <div class="panel-header">
                    <h2>{{ __('admin.dashboard.sales') }}</h2>
                    <span class="text-muted small">{{ __('admin.dashboard.total', ['amount' => '$' . number_format($chart->sum('total'))]) }}</span>
                </div>
                <div class="panel-body">
                    <div class="bar-chart">
                        @foreach ($chart as $day)
                            <div class="bar" title="{{ $day['label'] }}: ${{ $day['total'] }}">
                                <div class="fill" style="height: {{ max(2, round($day['total'] / $max * 100)) }}%"></div>
                                <small>{{ $day['label'] }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="panel h-100">
                <div class="panel-header">
                    <h2>{{ __('admin.dashboard.best_sellers') }}</h2>
                    <a href="{{ route('admin.pictures.index') }}" class="small fw-semibold">{{ __('admin.dashboard.all_pictures') }}</a>
                </div>
                <div class="panel-body d-grid gap-3">
                    @forelse ($topPictures as $picture)
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $picture->thumbnail_path }}" class="thumb" alt="">
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold text-truncate">{{ $picture->title }}</div>
                                <small class="text-muted">{{ __('admin.dashboard.by', ['name' => $picture->user?->name]) }}</small>
                            </div>
                            <span class="fw-bold">{{ $picture->orders_count }}</span>
                        </div>
                    @empty
                        <div class="empty"><i class="bi bi-images"></i>{{ __('admin.dashboard.no_pictures') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="panel">
                <div class="panel-header">
                    <h2>{{ __('admin.dashboard.recent_orders') }}</h2>
                    <a href="{{ route('admin.orders.index') }}" class="small fw-semibold">{{ __('admin.dashboard.all_orders') }}</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-admin">
                        <thead>
                            <tr>
                                <th>{{ __('admin.col.picture') }}</th>
                                <th>{{ __('admin.col.buyer') }}</th>
                                <th>{{ __('admin.col.price') }}</th>
                                <th>{{ __('admin.col.status') }}</th>
                                <th>{{ __('admin.col.date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $order->picture->thumbnail_path }}" class="thumb" alt="">
                                            <span class="fw-semibold">{{ $order->picture->title }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $order->user->name }}</td>
                                    <td class="fw-semibold">${{ $order->picture->price }}</td>
                                    <td>@include('admin.partials.order-status', ['status' => $order->status])</td>
                                    <td class="text-muted small">{{ $order->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty"><i class="bi bi-bag"></i>{{ __('admin.dashboard.no_orders') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
