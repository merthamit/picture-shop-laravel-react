@extends('admin.layouts.app')
@section('title', 'Users')
@section('subtitle', $users->total() . ' registered users')

@section('content')
    <div class="panel">
        <div class="panel-header">
            <form class="filters" method="GET">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                    placeholder="Search name or e-mail">
                <button class="btn btn-sm btn-light"><i class="bi bi-search"></i></button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-admin">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>E-mail</th>
                        <th>Language</th>
                        <th>Pictures</th>
                        <th>Purchases</th>
                        <th>Joined</th>
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
                                    <i class="bi bi-patch-check-fill text-primary" title="Verified"></i>
                                @else
                                    <span class="pill pill-warning ms-1">Unverified</span>
                                @endif
                            </td>
                            <td class="text-uppercase">{{ $user->locale }}</td>
                            <td>{{ $user->pictures_count }}</td>
                            <td>{{ $user->orders_count }}</td>
                            <td class="text-muted small">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <form action="{{ route('admin.users.destroy', $user) }}" method="post"
                                    data-confirm="Delete {{ $user->name }} and all of their pictures?">
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
                                <div class="empty"><i class="bi bi-people"></i>No users found</div>
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
