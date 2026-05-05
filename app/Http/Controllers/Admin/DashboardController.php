<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HireRequest;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_photographers' => User::photographers()->count(),
            'active_photographers' => User::photographers()->active()->count(),
            'blocked_photographers' => User::photographers()->blocked()->count(),
            'pending_requests' => HireRequest::pending()->count(),
            'accepted_requests' => HireRequest::accepted()->count(),
            'declined_requests' => HireRequest::declined()->count(),
            'today_registrations' => User::photographers()->whereDate('created_at', today())->count(),
            'total_requests' => HireRequest::count(),
        ];

        $recentPhotographers = User::photographers()
            ->with(['city', 'state', 'country'])
            ->latest()
            ->take(5)
            ->get();

        $recentRequests = HireRequest::with(['photographer', 'city'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentPhotographers', 'recentRequests'));
    }
}
