@foreach (['success' => 'bi-check-circle', 'error' => 'bi-exclamation-triangle', 'info' => 'bi-info-circle', 'warning' => 'bi-exclamation-circle'] as $type => $icon)
    @if (session()->has($type))
        <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show d-flex align-items-center gap-2"
            role="alert">
            <i class="bi {{ $icon }}"></i>
            {{ session()->get($type) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endforeach
