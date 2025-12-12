<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $logs = ActivityLog::query()
            ->with('user')
            ->when($q, fn($qq) => $qq->where(function ($w) use ($q) {
                $w->where('action', 'like', "%{$q}%")
                    ->orWhere('subject_type', 'like', "%{$q}%")
                    ->orWhere('subject_id', 'like', "%{$q}%");
            }))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity.index', compact('logs', 'q'));
    }
}
