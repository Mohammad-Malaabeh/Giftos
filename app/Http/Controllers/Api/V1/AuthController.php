<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'customer',
        ]);

        // Create token with abilities based on role
        $abilities = $this->getAbilitiesForRole($user->role);
        $token = $user->createToken('api_token', $abilities)->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new \App\Http\Resources\UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => now()->addDays(config('sanctum.expiration', 365)),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Rate limiting to prevent brute force attacks
        $throttleKey = Str::lower($data['email']) . '|' . $request->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, config('auth.throttle.login_attempts', 5))) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);

            return response()->json([
                'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
                'retry_after' => $seconds
            ], 429);
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            // Increment failed login attempts
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, config('auth.throttle.decay_minutes', 1) * 60);

            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if user is verified
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email address'
            ], 403);
        }

        // Clear rate limiter on successful login
        \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);

        // Revoke existing tokens if requested
        if ($request->boolean('revoke_tokens')) {
            $user->tokens()->delete();
        }

        // Create token with device-specific abilities
        $deviceName = $request->get('device_name', 'Unknown Device');
        $abilities = $this->getAbilitiesForRole($user->role);

        // Add device-specific abilities
        if ($request->boolean('remember_me')) {
            $abilities[] = 'remember-me';
        }

        $token = $user->createToken($deviceName, $abilities)->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => new \App\Http\Resources\UserResource($user->load(['roles', 'permissions'])),
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => now()->addDays(config('sanctum.expiration', 365)),
            'abilities' => $abilities,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        // Log the logout activity
        activity()
            ->causedBy($request->user())
            ->performedOn($token)
            ->withProperties(['ip' => $request->ip(), 'user_agent' => $request->userAgent()])
            ->log('User logged out');

        $token->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke all tokens
        $user->tokens()->delete();

        // Log the activity
        activity()
            ->causedBy($user)
            ->withProperties(['ip' => $request->ip(), 'user_agent' => $request->userAgent()])
            ->log('User logged out from all devices');

        return response()->json([
            'message' => 'Logged out from all devices successfully'
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'permissions']);

        return response()->json([
            'user' => new \App\Http\Resources\UserResource($user),
            'current_token' => [
                'id' => $request->user()->currentAccessToken()->id,
                'name' => $request->user()->currentAccessToken()->name,
                'abilities' => $request->user()->currentAccessToken()->abilities,
                'created_at' => $request->user()->currentAccessToken()->created_at,
                'expires_at' => $request->user()->currentAccessToken()->expires_at,
            ]
        ]);
    }

    public function tokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'tokens' => $tokens->map(function ($token) use ($request) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'created_at' => $token->created_at,
                    'expires_at' => $token->expires_at,
                    'last_used_at' => $token->last_used_at,
                    'is_current' => $token->id === $request->user()->currentAccessToken()->id,
                ];
            })
        ]);
    }

    public function revokeToken(Request $request, string $tokenId): JsonResponse
    {
        $user = $request->user();
        $token = $user->tokens()->find($tokenId);

        if (!$token) {
            return response()->json([
                'message' => 'Token not found'
            ], 404);
        }

        // Prevent revoking current token
        if ($token->id === $user->currentAccessToken()->id) {
            return response()->json([
                'message' => 'Cannot revoke current token. Use logout endpoint instead.'
            ], 422);
        }

        $token->delete();

        return response()->json([
            'message' => 'Token revoked successfully'
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'avatar' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['name', 'email']));

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $path = $avatar->store('avatars', 'public');

            // Create media record
            $user->media()->create([
                'filename' => $avatar->hashName(),
                'original_filename' => $avatar->getClientOriginalName(),
                'mime_type' => $avatar->getMimeType(),
                'size' => $avatar->getSize(),
                'path' => $path,
                'disk' => 'public',
                'is_primary' => true,
            ]);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new \App\Http\Resources\UserResource($user->load(['roles', 'permissions']))
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($data['password'])
        ]);

        // Revoke all tokens except current one
        $currentTokenId = $user->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        return response()->json([
            'message' => 'Password updated successfully',
            'note' => 'All other sessions have been logged out for security'
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = \Illuminate\Support\Facades\Password::sendResetLink($request->validated());

        if ($status == \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email'
            ]);
        }

        return response()->json([
            'message' => 'Unable to send password reset link'
        ], 422);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        $status = \Illuminate\Support\Facades\Password::reset($data, function ($user) use ($data) {
            $user->password = Hash::make($data['password']);
            $user->setRememberToken(Str::random(60));
            $user->save();

            event(new PasswordReset($user));
        });

        if ($status == \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password reset successfully'
            ]);
        }

        return response()->json([
            'message' => 'Unable to reset password'
        ], 422);
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse
    {
        $user = User::find($id);

        if (!$user || !hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Invalid verification link'
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ]);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'message' => 'Email verified successfully'
        ]);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent'
        ]);
    }

    private function getAbilitiesForRole(string $role): array
    {
        return match ($role) {
            'admin' => ['*'], // All abilities
            'manager' => [
                'products:*',
                'orders:*',
                'categories:*',
                'users:view',
                'analytics:view',
            ],
            'customer' => [
                'products:view',
                'orders:*',
                'profile:*',
                'reviews:*',
                'comments:*',
            ],
            default => ['profile:view'],
        };
    }
}
