@php
    $map = [
        'completed' => ['pill-success', __('admin.status.completed')],
        'pending' => ['pill-warning', __('admin.status.pending')],
        'failed' => ['pill-danger', __('admin.status.failed')],
    ];
    [$class, $label] = $map[$status] ?? ['pill-muted', ucfirst($status)];
@endphp
<span class="pill {{ $class }}">{{ $label }}</span>
