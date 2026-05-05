@extends('admin.layout')
@section('title', 'Dashboard')
@section('icon', 'bi-grid-1x2-fill')

@section('content')
<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-num">{{ $stats['total_photographers'] }}</div>
            <div class="stat-label">Total Photographers</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-num text-success">{{ $stats['active_photographers'] }}</div>
            <div class="stat-label">Active</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-num text-danger">{{ $stats['blocked_photographers'] }}</div>
            <div class="stat-label">Blocked</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-num text-info">{{ $stats['today_registrations'] }}</div>
            <div class="stat-label">Today's Registrations</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-num">{{ $stats['total_requests'] }}</div>
            <div class="stat-label">Total Requests</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-num" style="color:#92400e;">{{ $stats['pending_requests'] }}</div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-num text-success">{{ $stats['accepted_requests'] }}</div>
            <div class="stat-label">Accepted</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-num text-danger">{{ $stats['declined_requests'] }}</div>
            <div class="stat-label">Declined</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Photographers -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                Recent Photographers
                <a href="{{ route('admin.photographers') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>City</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($recentPhotographers as $p)
                        <tr>
                            <td><a href="{{ route('admin.photographers.show', $p->id) }}">{{ $p->name }}</a></td>
                            <td>{{ $p->city?->name ?? '-' }}</td>
                            <td><span class="badge badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                            <td class="text-muted small">{{ $p->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No photographers yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Requests -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                Recent Hire Requests
                <a href="{{ route('admin.hire-requests') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Photographer</th><th>Event</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($recentRequests as $r)
                        <tr>
                            <td>{{ $r->photographer?->name ?? '-' }}</td>
                            <td>{{ $r->event_type }}</td>
                            <td><span class="badge badge-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                            <td class="text-muted small">{{ $r->event_date->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No requests yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
