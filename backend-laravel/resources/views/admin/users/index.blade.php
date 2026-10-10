@extends('admin.layouts.app')
@section('title', __('admin.users.title'))
@section('subtitle', __('admin.users.count', ['count' => $users->total()]))

@section('content')
    <div class="panel">
        <div class="panel-header">
            <form class="filters" method="GET">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                    placeholder="{{ __('admin.filter.search_users') }}">
                <button class="btn btn-sm btn-light"><i class="bi bi-search"></i></button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-admin">
                <thead>
                    <tr>
                        <th>{{ __('admin.col.user') }}</th>
                        <th>{{ __('admin.col.email') }}</th>
                        <th>{{ __('admin.col.language') }}</th>
                        <th>{{ __('admin.col.pictures') }}</th>
                        <th>{{ __('admin.col.purchases') }}</th>
                        <th>{{ __('admin.col.joined') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    <span class="fw-semibold">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>
                                {{ $user->email }}
                                @if ($user->hasVerifiedEmail())
                                    <i class="bi bi-patch-check-fill text-primary" title="{{ __('admin.status.verified') }}"></i>
                                @else
                                    <span class="pill pill-warning ms-1">{{ __('admin.status.unverified') }}</span>
                                @endif
                            </td>
                            <td class="text-uppercase">{{ $user->locale }}</td>
                            <td>{{ $user->pictures_count }}</td>
                            <td>{{ $user->orders_count }}</td>
                            <td class="text-muted small">{{ $user->created_at->translatedFormat('d M Y') }}</td>
                            <td class="text-end">
                                <form action="{{ route('admin.users.destroy', $user) }}" method="post"
                                    data-confirm="{{ __('admin.users.confirm_delete', ['name' => $user->name]) }}">
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
                                <div class="empty"><i class="bi bi-people"></i>{{ __('admin.users.empty') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="panel-body border-top">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
