@extends('admin.layout')
@section('title', 'Hire Requests')
@section('icon', 'bi-send-fill')

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Search Photographer</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Name...">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                    <option value="invalidated" {{ request('status') == 'invalidated' ? 'selected' : '' }}>Invalidated</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-brand btn-sm w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- List -->
<div class="card">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <span>{{ $requests->total() }} request(s)</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr><th>Photographer</th><th>Event</th><th>Date</th><th>Location</th><th>Status</th><th>Created</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($requests as $r)
                <tr>
                    <td>
                        <a href="{{ route('admin.photographers.show', $r->photographer_id) }}" class="fw-semibold text-decoration-none">{{ $r->photographer?->name ?? '-' }}</a>
                    </td>
                    <td>{{ $r->event_type }}</td>
                    <td>{{ $r->event_date->format('d M Y') }}</td>
                    <td class="small">{{ $r->city?->name ?? '-' }}, {{ $r->state?->name ?? '' }}</td>
                    <td><span class="badge badge-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                    <td class="text-muted small">{{ $r->created_at->format('d M Y') }}</td>
                    <td>
                        @if($r->status === 'pending')
                            <form method="POST" action="{{ route('admin.hire-requests.invalidate', $r->id) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary" onclick="return confirm('Invalidate this request?')">Invalidate</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No hire requests found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
    <div class="card-footer">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
