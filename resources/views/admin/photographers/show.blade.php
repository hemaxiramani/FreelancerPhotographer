@extends('admin.layout')
@section('title', $photographer->name)
@section('icon', 'bi-person-fill')

@section('content')
<div class="row g-4">
    <!-- Profile Card -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @if($photographer->profile_photo)
                    <img src="{{ Storage::url($photographer->profile_photo) }}" class="rounded-circle mb-3" width="100" height="100" style="object-fit:cover;">
                @else
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width:100px;height:100px;font-size:36px;color:#588161;">
                        {{ strtoupper(substr($photographer->name, 0, 1)) }}
                    </div>
                @endif
                <h5>{{ $photographer->name }}</h5>
                <p class="text-muted small mb-1">{{ $photographer->email }}</p>
                <p class="text-muted small mb-3">{{ $photographer->phone ?? 'No phone' }}</p>
                <span class="badge badge-{{ $photographer->status }} mb-3">{{ ucfirst($photographer->status) }}</span>

                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('admin.hire-requests.create', $photographer->id) }}" class="btn btn-brand btn-sm">Send Hire Request</a>
                    @if($photographer->status === 'active')
                        <form method="POST" action="{{ route('admin.photographers.block', $photographer->id) }}">
                            @csrf
                            <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Block this photographer?')">Block</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.photographers.unblock', $photographer->id) }}">
                            @csrf
                            <button class="btn btn-outline-success btn-sm">Unblock</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Location -->
        <div class="card mt-3">
            <div class="card-header py-2"><i class="bi bi-geo-alt me-1"></i> Base Location</div>
            <div class="card-body small">
                <strong>{{ $photographer->city?->name ?? '-' }}</strong>, {{ $photographer->state?->name ?? '' }}<br>
                {{ $photographer->country?->name ?? '' }}
            </div>
        </div>

        <!-- Social Links -->
        <div class="card mt-3">
            <div class="card-header py-2"><i class="bi bi-link-45deg me-1"></i> Links</div>
            <div class="card-body small">
                @php $profile = $photographer->photographerProfile; @endphp
                @if($profile?->instagram_link)
                    <div class="mb-2"><i class="bi bi-instagram me-1"></i> <a href="{{ $profile->instagram_link }}" target="_blank">Instagram</a></div>
                @endif
                @if($profile?->facebook_link)
                    <div class="mb-2"><i class="bi bi-facebook me-1"></i> <a href="{{ $profile->facebook_link }}" target="_blank">Facebook</a></div>
                @endif
                @if($profile?->portfolio_link)
                    <div class="mb-2"><i class="bi bi-folder me-1"></i> <a href="{{ $profile->portfolio_link }}" target="_blank">Portfolio</a></div>
                @endif
                @if(!$profile?->instagram_link && !$profile?->facebook_link && !$profile?->portfolio_link)
                    <span class="text-muted">No links added</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="col-md-8">
        <!-- Bio & Experience -->
        <div class="card mb-3">
            <div class="card-header py-2">Bio & Experience</div>
            <div class="card-body">
                <p>{{ $profile?->bio ?? 'No bio added' }}</p>
                <strong>Experience:</strong> {{ $profile?->experience ?? 'Not specified' }}
            </div>
        </div>

        <!-- Categories & Charges -->
        <div class="card mb-3">
            <div class="card-header py-2">Categories & Charges</div>
            <div class="card-body">
                <div class="mb-2"><strong>Default Charge/Day:</strong> {{ $profile?->default_charge ? '₹' . number_format($profile->default_charge) : 'Not set' }}</div>
                @if($photographer->categories->count())
                <table class="table table-sm mt-2 mb-0">
                    <thead><tr><th>Category</th><th>Charge/Day</th></tr></thead>
                    <tbody>
                        @foreach($photographer->categories as $cat)
                        <tr>
                            <td>{{ $cat->name }}</td>
                            <td>{{ $cat->pivot->charge_per_day ? '₹' . number_format($cat->pivot->charge_per_day) : 'Default' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                    <span class="text-muted">No categories selected</span>
                @endif
            </div>
        </div>

        <!-- Camera Kit -->
        <div class="card mb-3">
            <div class="card-header py-2">Camera Kit ({{ $photographer->cameraKits->count() }} items)</div>
            <div class="card-body">
                @if($photographer->cameraKits->count())
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($photographer->cameraKits as $kit)
                            <span class="badge bg-light text-dark border">{{ $kit->item_name }}</span>
                        @endforeach
                    </div>
                @else
                    <span class="text-muted">No kit items added</span>
                @endif
            </div>
        </div>

        <!-- Work Cities -->
        <div class="card mb-3">
            <div class="card-header py-2">Work Cities ({{ $photographer->workCities->count() }})</div>
            <div class="card-body">
                @if($photographer->workCities->count())
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($photographer->workCities as $wc)
                            <span class="badge bg-light text-dark border">{{ $wc->city?->name }}, {{ $wc->state?->name }} ({{ $wc->country?->name }})</span>
                        @endforeach
                    </div>
                @else
                    <span class="text-muted">No work cities added</span>
                @endif
            </div>
        </div>

        <!-- Recent Requests -->
        <div class="card">
            <div class="card-header py-2">Recent Hire Requests</div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Date</th><th>Event</th><th>Location</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($photographer->hireRequests as $r)
                        <tr>
                            <td>{{ $r->event_date->format('d M Y') }}</td>
                            <td>{{ $r->event_type }}</td>
                            <td>{{ $r->city?->name ?? '-' }}</td>
                            <td><span class="badge badge-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No requests yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Danger Zone -->
<div class="card border-danger mt-4">
    <div class="card-header py-2 text-danger">Danger Zone</div>
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <strong>Delete this photographer</strong>
            <p class="text-muted small mb-0">This action cannot be undone. All data will be permanently removed.</p>
        </div>
        <form method="POST" action="{{ route('admin.photographers.destroy', $photographer->id) }}">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to permanently delete {{ $photographer->name }}?')">Delete Photographer</button>
        </form>
    </div>
</div>
@endsection
