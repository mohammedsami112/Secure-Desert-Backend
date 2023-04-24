<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

trait CanResetPassword
{
    /**
     * Get password broker.
     *
     * @return \Illuminate\Auth\Passwords\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }

    /**
     * Send a password reset link to the given user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = $this->broker()->sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? $this->respond('Password reset link sent', 200)
            : $this->fail('Failed to send password reset link', 500);
    }

    /**
     * Reset the given user's password.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
        ]);

        $status = $this->broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->respond('Password reset successfully', 200)
            : $this->fail('Failed to reset password', 500);
    }

    /**
     * Verify the given user's password reset token.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyToken(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
        ]);

        $user = $this->broker()->getUser($request->only('email'));

        return $user && $this->broker()->tokenExists($user, $request->token)
            ? $this->respond('Token verified', 200)
            : $this->fail('Invalid token', 500);
    }
}
