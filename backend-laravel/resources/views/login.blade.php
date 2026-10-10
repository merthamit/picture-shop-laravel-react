@extends('admin.layouts.app')
@section('title', 'Sign in')

@section('content')
    <div class="login-wrap">
        <div class="panel login-card">
            <div class="text-center mb-4">
                <span class="brand-logo"><i class="bi bi-shield-lock"></i></span>
                <h1 class="h4 fw-bold mb-1">Admin sign in</h1>
                <p class="text-muted mb-0">Manage the Picture Shop marketplace</p>
            </div>
            @include('admin.layouts.alerts')
            <form action="{{ route('admin.auth') }}" method="POST" novalidate>
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" autofocus
                        class="form-control @error('email') is-invalid @enderror" placeholder="admin@example.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold small">Password</label>
                    <input type="password" name="password" id="password"
                        class="form-control @error('password') is-invalid @enderror" placeholder="••••••••">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me">
                    <label class="form-check-label small" for="remember_me">Remember me</label>
                </div>
                <button class="btn btn-primary w-100 justify-content-center py-2" type="submit">
                    <i class="bi bi-box-arrow-in-right"></i> Sign in
                </button>
            </form>
        </div>
    </div>
@endsection
