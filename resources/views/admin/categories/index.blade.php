@extends('admin.layout')
@section('title', 'Categories')
@section('icon', 'bi-tag-fill')

@section('content')
<div class="row g-4">
    <!-- Add Category -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header py-3">Add Category</div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 small">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.categories.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Drone Photography" required maxlength="100">
                    </div>
                    <button type="submit" class="btn btn-brand btn-sm">Add Category</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Categories List -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header py-3">{{ $categories->count() }} Categories</div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Name</th><th>Photographers</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach($categories as $cat)
                        <tr>
                            <td>
                                <form method="POST" action="{{ route('admin.categories.update', $cat->id) }}" class="d-flex gap-2 align-items-center edit-cat-form" style="max-width:300px;">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" value="{{ $cat->name }}" class="form-control form-control-sm border-0 bg-transparent px-0 cat-input" readonly required maxlength="100">
                                    <button type="button" class="btn btn-sm btn-outline-secondary cat-edit-btn" title="Edit"><i class="bi bi-pencil"></i></button>
                                    <button type="submit" class="btn btn-sm btn-brand cat-save-btn" style="display:none;" title="Save"><i class="bi bi-check-lg"></i></button>
                                </form>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $cat->photographers_count }}</span></td>
                            <td>
                                <span class="badge {{ $cat->status ? 'badge-active' : 'badge-blocked' }}">{{ $cat->status ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.categories.toggle', $cat->id) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm {{ $cat->status ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                        {{ $cat->status ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.cat-edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = this.closest('.edit-cat-form');
        const input = form.querySelector('.cat-input');
        const saveBtn = form.querySelector('.cat-save-btn');
        input.readOnly = false;
        input.classList.remove('bg-transparent', 'border-0');
        input.focus();
        this.style.display = 'none';
        saveBtn.style.display = 'inline-block';
    });
});
</script>
@endpush
@endsection
