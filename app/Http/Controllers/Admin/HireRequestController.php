<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\HireRequest;
use App\Models\Notification;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\Request;

class HireRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = HireRequest::with(['photographer', 'country', 'state', 'city']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('photographer', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        return view('admin.hire-requests.index', compact('requests'));
    }

    public function create($photographer_id)
    {
        $photographer = User::photographers()->findOrFail($photographer_id);
        $countries = Country::active()->orderBy('name')->get();

        return view('admin.hire-requests.create', compact('photographer', 'countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'photographer_id' => 'required|exists:users,id',
            'event_date'      => 'required|date|after_or_equal:today',
            'event_type'      => 'required|string|max:100',
            'country_id'      => 'required|exists:countries,id',
            'state_id'        => 'required|exists:states,id',
            'city_id'         => 'required|exists:cities,id',
            'note'            => 'nullable|string|max:1000',
        ]);

        $hireRequest = HireRequest::create($validated);

        // Create notification record
        $photographer = User::find($validated['photographer_id']);
        $notification = Notification::create([
            'target_type' => 'specific',
            'title'       => 'New Hire Request',
            'message'     => "You have a new hire request for {$validated['event_date']} - {$validated['event_type']}",
            'sent_at'     => now(),
        ]);
        $notification->users()->attach($validated['photographer_id']);

        // Send FCM push notification
        if ($photographer) {
            \Illuminate\Support\Facades\Log::info('FCM: Sending hire request notification', [
                'photographer_id'   => $photographer->id,
                'photographer_name' => $photographer->name,
                'device_tokens'     => $photographer->deviceTokens()
                                        ->whereNotNull('fcm_token')
                                        ->pluck('fcm_token')
                                        ->map(fn($t) => substr($t, 0, 20) . '...')
                                        ->toArray(),
            ]);

            app(FcmService::class)->sendToUser($photographer, $notification->title, $notification->message, [
                'type'            => 'hire_request',
                'hire_request_id' => (string) $hireRequest->id,
            ]);
        }

        return redirect()->route('admin.hire-requests')->with('success', 'Hire request sent to ' . $photographer->name);
    }

    public function invalidate($id)
    {
        $request = HireRequest::findOrFail($id);

        if ($request->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be invalidated.');
        }

        $request->update(['status' => 'invalidated']);

        return back()->with('success', 'Request has been invalidated.');
    }
}
