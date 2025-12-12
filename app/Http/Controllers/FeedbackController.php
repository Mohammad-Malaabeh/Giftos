<?php

namespace App\Http\Controllers;

use App\Notifications\NewFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Feedback;
use App\Models\User;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:10|max:2000',
            'type' => 'required|in:bug,feature,suggestion,other',
            'page_url' => 'nullable|url',
        ]);

        $feedback = Auth::check()
            ? Auth::user()->feedback()->create($validated)
            : Feedback::create($validated);

        // Notify admins
        if (class_exists(NewFeedback::class)) {
            User::role('admin')->get()->each->notify(
                new NewFeedback($feedback)
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Thank you for your feedback!',
                'feedback' => $feedback
            ]);
        }

        return back()->with('success', 'Thank you for your feedback!');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Feedback::class);

        $query = Feedback::with('user')
            ->latest();

        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return view('admin.feedback.index', [
            'feedback' => $query->paginate(20),
            'types' => ['bug' => 'Bug Report', 'feature' => 'Feature Request', 'suggestion' => 'Suggestion', 'other' => 'Other'],
            'statuses' => ['new' => 'New', 'read' => 'Read', 'in_progress' => 'In Progress', 'resolved' => 'Resolved']
        ]);
    }

    public function show(Feedback $feedback)
    {
        $this->authorize('view', $feedback);

        if ($feedback->status === 'new') {
            $feedback->markAsRead();
        }

        return view('admin.feedback.show', [
            'feedback' => $feedback->load('user'),
            'types' => ['bug' => 'Bug Report', 'feature' => 'Feature Request', 'suggestion' => 'Suggestion', 'other' => 'Other'],
            'statuses' => ['new' => 'New', 'read' => 'Read', 'in_progress' => 'In Progress', 'resolved' => 'Resolved']
        ]);
    }

    public function update(Request $request, Feedback $feedback)
    {
        $this->authorize('update', $feedback);

        $validated = $request->validate([
            'status' => 'required|in:new,read,in_progress,resolved',
            'admin_notes' => 'nullable|string',
        ]);

        $feedback->update([
            'status' => $validated['status'],
            'metadata' => array_merge($feedback->metadata ?? [], [
                'admin_notes' => $validated['admin_notes'] ?? null,
                'admin_updated_at' => now()->toDateTimeString(),
                'admin_updated_by' => auth()->id(),
            ])
        ]);

        return back()->with('success', 'Feedback updated successfully');
    }

    /**
     * Delete feedback (admin)
     */
    public function destroy(Feedback $feedback)
    {
        $this->authorize('delete', $feedback);

        $feedback->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Feedback deleted'], 200);
        }

        return redirect("admin/feedback")->with('success', 'Feedback deleted');
    }
}
