<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->notifications()->latest()->paginate(20)
        );
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->update([
            'read_at' => now(),
        ]);

        ActivityLogger::log(
            'read',
            'candidate_notifications',
            "Candidate notification {$notification->id} marked as read.",
            $request,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $notification->fresh(),
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->notifications()->whereNull('read_at')->update([
            'read_at' => now(),
        ]);

        ActivityLogger::log(
            'read_all',
            'candidate_notifications',
            "Candidate {$request->user()->email} marked all notifications as read.",
            $request,
            $request->user()->id
        );

        return response()->json([
            'message' => 'All notifications marked as read.',
        ]);
    }
}
