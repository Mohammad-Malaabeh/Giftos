<?php

namespace App\Support;

use App\Models\ActivityLog;

class Activity
{
    public static function log(string $action, $subject = null, array $properties = []): void
    {
        $user = auth()->user();
        $req = request();

        ActivityLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject->id ?? null,
            'properties' => $properties ?: null,
            'ip' => $req?->ip(),
        ]);
    }
}
