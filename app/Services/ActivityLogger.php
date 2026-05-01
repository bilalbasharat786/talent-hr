<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogger
{
    public static function log(
        string $action,
        string $module,
        ?string $description = null,
        ?Request $request = null,
        ?int $userId = null
    ): void {
        $request = $request ?: request();

        ActivityLog::create([
            'user_id' => $userId ?: optional($request->user())->id,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}

