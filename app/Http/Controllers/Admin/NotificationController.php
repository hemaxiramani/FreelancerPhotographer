<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::with('users')
            ->orderBy('sent_at', 'desc')
            ->paginate(15);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        $photographers = User::photographers()->active()->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.notifications.create', compact('photographers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_type'  => 'required|in:all,specific',
            'user_ids'     => 'required_if:target_type,specific|array',
            'user_ids.*'   => 'exists:users,id',
            'title'        => 'required|string|max:255',
            'message'      => 'required|string|max:1000',
        ]);

        $notification = Notification::create([
            'target_type' => $validated['target_type'],
            'title'       => $validated['title'],
            'message'     => $validated['message'],
            'sent_at'     => now(),
        ]);

        $fcm = app(FcmService::class);

        if ($validated['target_type'] === 'all') {
            // Attach to all active photographers
            $userIds = User::photographers()->active()->pluck('id')->toArray();
            foreach ($userIds as $uid) {
                $notification->users()->attach($uid);
            }
            $fcm->sendToUsers($userIds, $validated['title'], $validated['message'], [
                'type'            => 'custom_notification',
                'notification_id' => $notification->id,
            ]);
            $count = count($userIds);
        } else {
            // Attach to specific users
            foreach ($validated['user_ids'] as $uid) {
                $notification->users()->attach($uid);
            }
            $fcm->sendToUsers($validated['user_ids'], $validated['title'], $validated['message'], [
                'type'            => 'custom_notification',
                'notification_id' => $notification->id,
            ]);
            $count = count($validated['user_ids']);
        }

        return redirect()->route('admin.notifications')->with('success', "Notification sent to $count photographer(s).");
    }
}
