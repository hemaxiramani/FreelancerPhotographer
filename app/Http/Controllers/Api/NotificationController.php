<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/notifications
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
                                 ->appNotifications()
                                 ->orderBy('sent_at', 'desc')
                                 ->paginate($request->get('per_page', 20));

        return $this->success($notifications);
    }

    /**
     * PUT /api/v1/notifications/{id}/read
     */
    public function markRead(Request $request, $id)
    {
        $user = $request->user();

        $notification = $user->appNotifications()->where('notifications.id', $id)->first();

        if (! $notification) {
            return $this->notFound('Notification not found');
        }

        if (! $notification->pivot->read_at) {
            $user->appNotifications()->updateExistingPivot($id, [
                'read_at' => now(),
            ]);
        }

        return $this->success(null, 'Notification marked as read');
    }
}
