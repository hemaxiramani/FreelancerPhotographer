@extends('admin.layout')
@section('title', 'Photographers')
@section('icon', 'bi-people-fill')

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="form-label small">Search Name</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Name...">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">Country</label>
                    <select name="country_id" id="filterCountry" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}" {{ request('country_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">State</label>
                    <select name="state_id" id="filterState" class="form-select form-select-sm">
                        <option value="">All</option>
                        @if(request('state_id') && isset($states))
                            @foreach($states as $s)
                                <option value="{{ $s->id }}" {{ request('state_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small">City</label>
                    <select name="city_id" id="filterCity" class="form-select form-select-sm">
                        <option value="">All</option>
                        @if(request('city_id') && isset($cities))
                            @foreach($cities as $ct)
                                <option value="{{ $ct->id }}" {{ request('city_id') == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label small">Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-1">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <!-- Preserve sort -->
                    @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif
                    @if(request('dir'))
                        <input type="hidden" name="dir" value="{{ request('dir') }}">
                    @endif
                    <button type="submit" class="btn btn-brand btn-sm flex-fill"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="{{ route('admin.photographers') }}" class="btn btn-outline-secondary btn-sm" title="Clear"><i class="bi bi-x-lg"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- List -->
<div class="card">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <span>{{ $photographers->total() }} photographer(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'dir' => request('sort') == 'name' && request('dir') == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                            Name
                            @if(request('sort') == 'name')
                                <i class="bi bi-chevron-{{ request('dir') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th>Location</th>
                    <th>Categories</th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'charge', 'dir' => request('sort') == 'charge' && request('dir') == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                            Charge/Day
                            @if(request('sort') == 'charge')
                                <i class="bi bi-chevron-{{ request('dir') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th>Status</th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'date', 'dir' => request('sort') == 'date' && request('dir') == 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                            Joined
                            @if(request('sort') == 'date')
                                <i class="bi bi-chevron-{{ request('dir') == 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($photographers as $p)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($p->profile_photo)
                                <img src="{{ asset('storage/' . $p->profile_photo) }}" alt="" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;background:var(--brand-light);color:var(--brand);font-weight:600;font-size:14px;">
                                    {{ strtoupper(substr($p->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <div class="fw-semibold">{{ $p->name }}</div>
                                <div class="text-muted small">{{ $p->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="small">
                        @if($p->city || $p->state)
                            {{ $p->city?->name ?? '-' }}, {{ $p->state?->name ?? '' }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @foreach($p->categories->take(2) as $cat)
                            <span class="badge bg-light text-dark border" style="font-size:11px;">{{ $cat->name }}</span>
                        @endforeach
                        @if($p->categories->count() > 2)
                            <span class="text-muted small">+{{ $p->categories->count() - 2 }}</span>
                        @endif
                        @if($p->categories->isEmpty())
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>{{ $p->photographerProfile?->default_charge ? '₹' . number_format($p->photographerProfile->default_charge) : '—' }}</td>
                    <td><span class="badge badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                    <td class="text-muted small">{{ $p->created_at->format('d M Y') }}</td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('admin.photographers.show', $p->id) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.hire-requests.create', $p->id) }}" class="btn btn-sm btn-brand" title="Hire"><i class="bi bi-send"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No photographers found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($photographers->hasPages())
    <div class="card-footer d-flex justify-content-center">{{ $photographers->links() }}</div>
    @endif
</div>

@push('scripts')
<script>
// Cascading: Country → State → City
const countryEl = document.getElementById('filterCountry');
const stateEl = document.getElementById('filterState');
const cityEl = document.getElementById('filterCity');

countryEl?.addEventListener('change', function() {
    stateEl.innerHTML = '<option value="">All</option>';
    cityEl.innerHTML = '<option value="">All</option>';
    if (!this.value) return;
    fetch(`{{ route('admin.api.states') }}?country_id=${this.value}`)
        .then(r => r.json())
        .then(data => data.forEach(s => {
            stateEl.innerHTML += `<option value="${s.id}">${s.name}</option>`;
        }));
});

stateEl?.addEventListener('change', function() {
    cityEl.innerHTML = '<option value="">All</option>';
    if (!this.value) return;
    fetch(`{{ route('admin.api.cities') }}?state_id=${this.value}`)
        .then(r => r.json())
        .then(data => data.forEach(c => {
            cityEl.innerHTML += `<option value="${c.id}">${c.name}</option>`;
        }));
});

// On page load, if country/state are selected, reload states/cities
@if(request('country_id'))
    fetch(`{{ route('admin.api.states') }}?country_id={{ request('country_id') }}`)
        .then(r => r.json())
        .then(data => {
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.text = s.name;
                if (s.id == {{ request('state_id', 0) }}) opt.selected = true;
                stateEl.appendChild(opt);
            });
            @if(request('state_id'))
                return fetch(`{{ route('admin.api.cities') }}?state_id={{ request('state_id') }}`);
            @endif
        })
        @if(request('state_id'))
        .then(r => r?.json())
        .then(data => {
            if (!data) return;
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.text = c.name;
                if (c.id == {{ request('city_id', 0) }}) opt.selected = true;
                cityEl.appendChild(opt);
            });
        })
        @endif
        ;
@endif
</script>
@endpush
@endsection
