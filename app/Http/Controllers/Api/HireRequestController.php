<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\FcmService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class HireRequestController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/hire-requests
     */
    public function index(Request $request)
    {
        $query = $request->user()
                         ->hireRequests()
                         ->with(['country:id,name', 'state:id,name', 'city:id,name']);

        // Optional status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 20));

        return $this->success($requests);
    }

    /**
     * GET /api/v1/hire-requests/{id}
     */
    public function show(Request $request, $id)
    {
        $hireRequest = $request->user()
                               ->hireRequests()
                               ->with(['country:id,name', 'state:id,name', 'city:id,name'])
                               ->find($id);

        if (! $hireRequest) {
            return $this->notFound('Hire request not found');
        }

        return $this->success($hireRequest);
    }

    /**
     * PUT /api/v1/hire-requests/{id}/respond
     */
    public function respond(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,declined',
        ]);

        $hireRequest = $request->user()
                               ->hireRequests()
                               ->find($id);

        if (! $hireRequest) {
            return $this->notFound('Hire request not found');
        }

        if ($hireRequest->status !== 'pending') {
            return $this->error('This request has already been ' . $hireRequest->status, 422);
        }

        $hireRequest->update(['status' => $validated['status']]);

        // Notify all admins about the photographer's response
        $photographer = $request->user();
        $statusLabel = ucfirst($validated['status']);
        $adminIds = User::where('role', 'admin')->pluck('id')->toArray();

        if (!empty($adminIds)) {
            $notification = Notification::create([
                'target_type' => 'specific',
                'title'       => "Hire Request {$statusLabel}",
                'message'     => "{$photographer->name} has {$validated['status']} the hire request for {$hireRequest->event_type} on {$hireRequest->event_date}",
                'sent_at'     => now(),
            ]);
            foreach ($adminIds as $adminId) {
                $notification->users()->attach($adminId);
            }

            app(FcmService::class)->sendToUsers($adminIds, $notification->title, $notification->message, [
                'type'            => 'hire_request_response',
                'hire_request_id' => (string) $hireRequest->id,
                'status'          => $validated['status'],
            ]);
        }

        return $this->success($hireRequest->fresh(), 'Request ' . $validated['status']);
    }
}
