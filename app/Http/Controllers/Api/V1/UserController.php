<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with(['roles']) // Eager load roles
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->role, function ($query, $role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => \App\Http\Resources\UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ]
        ]);
    }

    /**
     * Display the specified user (admin only)
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $user->load([
            'roles',
            'orders' => function ($query) {
                $query->latest()->limit(5);
            }
        ]);

        return response()->json([
            'data' => new \App\Http\Resources\UserResource($user)
        ]);
    }

    /**
     * Update user role (admin only)
     */
    public function updateRole(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user); // Or custom 'manageRoles' ability

        $validator = validator($request->all(), [
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->syncRoles([$request->role]);

        return response()->json([
            'message' => 'User role updated',
            'data' => new \App\Http\Resources\UserResource($user->load('roles'))
        ]);
    }

    /**
     * Ban a user (admin only)
     */
    public function ban(User $user): JsonResponse
    {
        $this->authorize('update', $user); // Or custom 'ban' ability

        if ($user->hasRole('admin')) {
            return response()->json([
                'message' => 'Cannot ban admin users'
            ], 422);
        }

        $user->update(['banned_at' => now()]);
        $user->tokens()->delete();

        return response()->json([
            'message' => 'User banned successfully',
            'data' => new \App\Http\Resources\UserResource($user)
        ]);
    }

    /**
     * Unban a user (admin only)
     */
    public function unban(User $user): JsonResponse
    {
        $this->authorize('update', $user); // Or custom 'unban' ability

        $user->update(['banned_at' => null]);

        return response()->json([
            'message' => 'User unbanned successfully',
            'data' => new \App\Http\Resources\UserResource($user)
        ]);
    }

    /**
     * Remove the specified user (admin only)
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        if ($user->hasRole('admin')) {
            return response()->json([
                'message' => 'Cannot delete admin users'
            ], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
