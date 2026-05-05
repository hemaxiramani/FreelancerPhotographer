@extends('admin.layout')
@section('title', 'Locations')
@section('icon', 'bi-geo-alt-fill')

@section('content')
<!-- Breadcrumb Navigation -->
<div class="card mb-4">
    <div class="card-body py-2">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item {{ !$selectedCountry ? 'active' : '' }}">
                    @if($selectedCountry)
                        <a href="{{ route('admin.locations') }}">Countries ({{ $countries->count() }})</a>
                    @else
                        Countries ({{ $countries->count() }})
                    @endif
                </li>
                @if($selectedCountry)
                <li class="breadcrumb-item {{ !$selectedState ? 'active' : '' }}">
                    @if($selectedState)
                        <a href="{{ route('admin.locations', ['country_id' => $selectedCountry->id]) }}">{{ $selectedCountry->name }} — States ({{ $states->count() }})</a>
                    @else
                        {{ $selectedCountry->name }} — States ({{ $states->count() }})
                    @endif
                </li>
                @endif
                @if($selectedState)
                <li class="breadcrumb-item active">{{ $selectedState->name }} — Cities ({{ $cities->count() }})</li>
                @endif
            </ol>
        </nav>
    </div>
</div>

@if($selectedState)
    {{-- ════════════ CITIES VIEW ════════════ --}}
    <!-- Add City Form -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.locations.store-city') }}" class="row g-2 align-items-end">
                @csrf
                <input type="hidden" name="state_id" value="{{ $selectedState->id }}">
                <div class="col-md-4">
                    <label class="form-label small">Add City to {{ $selectedState->name }}</label>
                    <input type="text" name="name" class="form-control form-control-sm" placeholder="City name" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-brand btn-sm">Add City</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>City</th><th>Source</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($cities as $city)
                    <tr>
                        <td>{{ $city->name }}</td>
                        <td><span class="badge {{ $city->is_user_added ? 'bg-info' : 'bg-light text-dark border' }}" style="font-size:11px;">{{ $city->is_user_added ? 'Admin Added' : 'GeoNames' }}</span></td>
                        <td>
                            <span class="badge {{ $city->status ? 'badge-active' : 'badge-blocked' }}">{{ $city->status ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.locations.toggle', ['type' => 'city', 'id' => $city->id]) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm {{ $city->status ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                    {{ $city->status ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No cities found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@elseif($selectedCountry)
    {{-- ════════════ STATES VIEW ════════════ --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>State</th><th>Code</th><th>Cities</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($states as $state)
                    <tr>
                        <td>
                            <a href="{{ route('admin.locations', ['country_id' => $selectedCountry->id, 'state_id' => $state->id]) }}" class="fw-semibold text-decoration-none">
                                {{ $state->name }}
                            </a>
                        </td>
                        <td class="text-muted small">{{ $state->state_code ?? '-' }}</td>
                        <td>{{ $state->cities_count }}</td>
                        <td>
                            <span class="badge {{ $state->status ? 'badge-active' : 'badge-blocked' }}">{{ $state->status ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.locations.toggle', ['type' => 'state', 'id' => $state->id]) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm {{ $state->status ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                    {{ $state->status ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No states found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@else
    {{-- ════════════ COUNTRIES VIEW ════════════ --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Country</th><th>ISO</th><th>States</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($countries as $country)
                    <tr>
                        <td>
                            <a href="{{ route('admin.locations', ['country_id' => $country->id]) }}" class="fw-semibold text-decoration-none">
                                {{ $country->name }}
                            </a>
                        </td>
                        <td class="text-muted small">{{ $country->iso2 }}</td>
                        <td>{{ $country->states_count }}</td>
                        <td>
                            <span class="badge {{ $country->status ? 'badge-active' : 'badge-blocked' }}">{{ $country->status ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.locations.toggle', ['type' => 'country', 'id' => $country->id]) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm {{ $country->status ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                    {{ $country->status ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
