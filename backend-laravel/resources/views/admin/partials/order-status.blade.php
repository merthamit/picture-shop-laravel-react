@php
    $map = [
        'completed' => ['pill-success', 'Completed'],
        'pending' => ['pill-warning', 'Processing'],
        'failed' => ['pill-danger', 'Failed'],
    ];
    [$class, $label] = $map[$status] ?? ['pill-muted', ucfirst($status)];
@endphp
<span class="pill {{ $class }}">{{ $label }}</span>
