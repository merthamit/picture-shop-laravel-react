{{-- Shared create / edit form --}}
<div class="panel" style="max-width: 560px">
    <form action="{{ $action }}" method="post" class="panel-body">
        @csrf
        @isset($method)
            @method($method)
        @endisset
        <label for="name" class="form-label fw-semibold small">Name</label>
        <input type="text" name="name" id="name" placeholder="e.g. Architecture"
            value="{{ old('name', $category->name ?? '') }}" autofocus
            class="form-control @error('name') is-invalid @enderror">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary"><i class="bi bi-check2"></i> Save</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </form>
</div>
