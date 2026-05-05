<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->company;

        $request->validate([
            'type' => ['nullable', Rule::in(['company_verification', 'hr_activity', 'system_alert'])],
            'status' => ['nullable', Rule::in(['read', 'unread'])],
        ]);

        $notifications = Notification::query()
            ->where(function ($query) use ($company, $request) {
                $query->where('company_id', $company->id)
                    ->orWhere('user_id', $request->user()->id);
            })
            ->when($request->type, function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->status === 'read', function ($query) {
                $query->whereNotNull('read_at');
            })
            ->when($request->status === 'unread', function ($query) {
                $query->whereNull('read_at');
            })
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        $company = $request->user()->company;

        if ($notification->company_id !== $company->id && $notification->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->update([
            'read_at' => now(),
        ]);

        ActivityLogger::log(
            'read',
            'company_notifications',
            "Notification {$notification->id} marked as read.",
            $request
        );

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $notification->fresh(),
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $company = $request->user()->company;

        Notification::query()
            ->where(function ($query) use ($company, $request) {
                $query->where('company_id', $company->id)
                    ->orWhere('user_id', $request->user()->id);
            })
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        ActivityLogger::log(
            'read_all',
            'company_notifications',
            "Company {$company->name} marked all notifications as read.",
            $request
        );

        return response()->json([
            'message' => 'All notifications marked as read.',
        ]);
    }
}

