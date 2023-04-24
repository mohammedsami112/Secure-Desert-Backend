<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserLoginRequest;
use App\Http\Requests\Auth\UserRegisterRequest;
use App\Http\Requests\Auth\UserUpdateProfileRequest;
use App\Models\User;
use App\Models\Verification;
use App\Traits\CanResetPassword;
use App\Traits\HasAuthSessions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mail;

class SessionsController extends Controller
{
    use HasAuthSessions, CanResetPassword;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $guest = [
            'login',
            'register',
            'forgotPassword',
            'resetPassword',
            'verifyToken',
        ];

        $this->middleware('auth:users')->except($guest);
        $this->middleware('guest:users')->only($guest);
    }

    /**
     * Get a Bearer token via given credentials.
     *
     * @param  UserLoginRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(UserLoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->fail('Invalid credentials', 401, [
                'email' => ['Invalid email or password'],
                'password' => ['Invalid email or password'],
            ]);
        }

        if (! $user->is_active) {
            return $this->fail('User is banned', 403);
        }

        if (! $user->email_verified_at && !$request->admin) {
            return $this->fail('Email must be Verified', 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->respond('User logged in', 200, [
            'token' => $token,
            'data' => $user,
        ]);
    }

    /**
     * Register a User.
     *
     * @param  UserRegisterRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(UserRegisterRequest $request)
    {
        User::create($request->parsed());

        return $this->sendVerification($request);
    }

    /**
     * Send Verification to User.
     *
     * @param  UserRegisterRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendVerification(UserRegisterRequest $request)
    {
        $token = Str::random(64);
        Verification::create([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now(),
        ]);

        Mail::send('emails.emailVerification', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Email Verification Mail');
        });

        return $this->respond('We have sent a verification mail', 201);
    }

    /**
     * Varify a User.
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyToken(Request $request)
    {
        $verify = Verification::where('token', $request->token)->first();
        if (!$verify) {
            return $this->respond('We can\'t verify this account please try again', 404);
        }

        $verify->delete();
        User::where('email', $verify->email)->update(['email_verified_at' => Carbon::now()]);

        return $this->respond('Your account has been verified, you can login now', 200);
    }

    /**
     * Resend Email Verification.
     *
     * @param  string  $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendVerification($email)
    {
        $verify = Verification::where('token', $email)->first();
        if (!$verify) {
            return $this->respond('We can\'t find your email in our record', 404);
        }

        Mail::send('email.emailVerificationEmail', ['token' => $verify->token], function ($message) use ($verify) {
            $message->to($verify->email);
            $message->subject('Email Verification Mail');
        });

        return $this->respond('We have sent a verification mail', 200);
    }

    /**
     * Update the authenticated User.
     *
     * @param  UserUpdateProfileRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserUpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->parsed());

        return $this->respond('User profile updated', 200, $user);
    }
}
