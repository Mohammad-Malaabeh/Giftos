<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class Activity
{
    /**
     * Log an activity.
     *
     * @param string $action The action being performed (e.g., 'product.created', 'order.updated')
     * @param Model|null $subject The model the action is being performed on
     * @param array $properties Additional properties to store with the activity
     * @param int|null $userId The user ID performing the action (defaults to current authenticated user)
     * @return ActivityLog
     */
    public static function log(string $action, ?Model $subject = null, array $properties = [], ?int $userId = null): ActivityLog
    {
        $userId = $userId ?? auth()->id();

        return ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'properties' => $properties,
            'ip' => request()?->ip(),
        ]);
    }

    /**
     * Log a user's activity.
     *
     * @param string $action
     * @param array $properties
     * @return ActivityLog
     */
    public static function logUser(string $action, array $properties = []): ActivityLog
    {
        return self::log($action, null, $properties);
    }

    /**
     * Log a model activity.
     *
     * @param string $action
     * @param Model $subject
     * @param array $properties
     * @return ActivityLog
     */
    public static function logModel(string $action, Model $subject, array $properties = []): ActivityLog
    {
        return self::log($action, $subject, $properties);
    }

    /**
     * Get activity logs for a specific subject.
     *
     * @param Model $subject
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function forSubject(Model $subject): \Illuminate\Database\Eloquent\Builder
    {
        return ActivityLog::where('subject_type', get_class($subject))
            ->where('subject_id', $subject->id);
    }

    /**
     * Get activity logs for a specific user.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function forUser(int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return ActivityLog::where('user_id', $userId);
    }

    /**
     * Get activity logs by action.
     *
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function byAction(string $action): \Illuminate\Database\Eloquent\Builder
    {
        return ActivityLog::where('action', $action);
    }
}
