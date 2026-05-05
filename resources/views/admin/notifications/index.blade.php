@extends('admin.layout')
@section('title', 'Notifications')
@section('icon', 'bi-bell-fill')

@section('content')
<div class="card">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <span>Notification History</span>
        <a href="{{ route('admin.notifications.create') }}" class="btn btn-brand btn-sm">Send New Notification</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr><th>Title</th><th>Message</th><th>Target</th><th>Recipients</th><th>Sent At</th></tr>
            </thead>
            <tbody>
                @forelse($notifications as $n)
                <tr>
                    <td class="fw-semibold">{{ $n->title }}</td>
                    <td class="small" style="max-width:300px;">{{ Str::limit($n->message, 80) }}</td>
                    <td><span class="badge {{ $n->target_type === 'all' ? 'bg-info' : 'bg-secondary' }}">{{ ucfirst($n->target_type) }}</span></td>
                    <td>{{ $n->users->count() }}</td>
                    <td class="text-muted small">{{ $n->sent_at ? \Carbon\Carbon::parse($n->sent_at)->format('d M Y, h:i A') : '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No notifications sent yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($notifications->hasPages())
    <div class="card-footer">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
