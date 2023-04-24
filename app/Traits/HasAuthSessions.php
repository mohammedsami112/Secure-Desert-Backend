<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HasAuthSessions
{
    /**
     * Get the authenticated User.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(Request $request)
    {
        return $this->respond('User profile', 200, $request->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->respond('Logged out', 200);
    }

    /**
     * Refresh a token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $token = $request->user()->createToken('auth_token')->plainTextToken;

        return $this->respond('Token refreshed', 200, ['token' => $token]);
    }

    /**
     * Get all of the authentication tokens for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sessions(Request $request)
    {
        $sessions = $request->user()->tokens()->orderBy('last_used_at', 'desc')->get();

        return $this->respond('Sessions', 200, $sessions->map(function ($token) {
            return $token->only(['id', 'created_at', 'last_used_at', 'expires_at']);
        }));
    }

    /**
     * Destroy the given authentication token.
     *
     * @param  int|string  $session
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroySession(int|string $session, Request $request)
    {
        if ($request->user()->tokens()->where('id', $session)->delete()) {
            return $this->respond('Session deleted', 200);
        }

        return $this->fail('Session not found', 404);
    }
}
