{{-- EN / TR switcher, remembered in the session --}}
<div class="btn-group btn-group-sm lang-switch" role="group" aria-label="{{ __('admin.language') }}">
    @foreach (['en' => 'EN', 'tr' => 'TR'] as $code => $label)
        <a href="{{ route('admin.locale', $code) }}"
            class="btn {{ app()->getLocale() === $code ? 'btn-primary' : 'btn-light' }}">{{ $label }}</a>
    @endforeach
</div>
