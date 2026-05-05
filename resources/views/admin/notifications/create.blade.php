@extends('admin.layout')
@section('title', 'Send Notification')
@section('icon', 'bi-bell-fill')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header py-3">Send Push Notification</div>
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

                <form method="POST" action="{{ route('admin.notifications.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target</label>
                        <select name="target_type" id="targetType" class="form-select" required>
                            <option value="all" {{ old('target_type') == 'all' ? 'selected' : '' }}>All Active Photographers</option>
                            <option value="specific" {{ old('target_type') == 'specific' ? 'selected' : '' }}>Specific Photographers</option>
                        </select>
                    </div>

                    <div class="mb-3" id="specificUsersGroup" style="display:none;">
                        <label class="form-label small fw-semibold">Select Photographers</label>
                        <div class="border rounded p-2" style="max-height:200px;overflow-y:auto;">
                            @foreach($photographers as $p)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="user_ids[]" value="{{ $p->id }}" id="user{{ $p->id }}"
                                    {{ is_array(old('user_ids')) && in_array($p->id, old('user_ids')) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="user{{ $p->id }}">{{ $p->name }} <span class="text-muted">({{ $p->email }})</span></label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Notification title" required maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Message</label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Notification message..." required maxlength="1000">{{ old('message') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-brand">Send Notification</button>
                        <a href="{{ route('admin.notifications') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const targetType = document.getElementById('targetType');
const specificGroup = document.getElementById('specificUsersGroup');

function toggleSpecific() {
    specificGroup.style.display = targetType.value === 'specific' ? 'block' : 'none';
}

targetType.addEventListener('change', toggleSpecific);
toggleSpecific();
</script>
@endpush
@endsection
