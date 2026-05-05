@extends('admin.layout')
@section('title', 'Send Hire Request')
@section('icon', 'bi-send-fill')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header py-3">
                Send Hire Request to <strong>{{ $photographer->name }}</strong>
            </div>
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

                <form method="POST" action="{{ route('admin.hire-requests.store') }}">
                    @csrf
                    <input type="hidden" name="photographer_id" value="{{ $photographer->id }}">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Event Type</label>
                        <input type="text" name="event_type" class="form-control" value="{{ old('event_type') }}" placeholder="e.g. Wedding, Corporate Event" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Event Date</label>
                        <input type="date" name="event_date" class="form-control" value="{{ old('event_date') }}" min="{{ date('Y-m-d') }}" required>
                    </div>

                    <!-- Cascading Location -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Country</label>
                            <select name="country_id" id="hireCountry" class="form-select" required>
                                <option value="">Select Country</option>
                                @foreach($countries as $c)
                                    <option value="{{ $c->id }}" {{ old('country_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">State</label>
                            <select name="state_id" id="hireState" class="form-select" required>
                                <option value="">Select State</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">City</label>
                            <select name="city_id" id="hireCity" class="form-select" required>
                                <option value="">Select City</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Note <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Any additional details...">{{ old('note') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-brand">Send Request</button>
                        <a href="{{ route('admin.photographers.show', $photographer->id) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('hireCountry')?.addEventListener('change', function() {
    const stateSelect = document.getElementById('hireState');
    const citySelect = document.getElementById('hireCity');
    stateSelect.innerHTML = '<option value="">Select State</option>';
    citySelect.innerHTML = '<option value="">Select City</option>';
    if (!this.value) return;
    fetch(`{{ route('admin.api.states') }}?country_id=${this.value}`)
        .then(r => r.json())
        .then(data => data.forEach(s => {
            stateSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`;
        }));
});

document.getElementById('hireState')?.addEventListener('change', function() {
    const citySelect = document.getElementById('hireCity');
    citySelect.innerHTML = '<option value="">Select City</option>';
    if (!this.value) return;
    fetch(`{{ route('admin.api.cities') }}?state_id=${this.value}`)
        .then(r => r.json())
        .then(data => data.forEach(c => {
            citySelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
        }));
});
</script>
@endpush
@endsection
