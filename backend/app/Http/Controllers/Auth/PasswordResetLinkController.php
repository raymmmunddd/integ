<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Send password reset OTP to user's email
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Check rate limiting (max 3 requests per 10 minutes per email)
        $rateLimitKey = 'password_reset_' . $request->email;
        $attempts = Cache::get($rateLimitKey, 0);
        
        if ($attempts >= 3) {
            throw ValidationException::withMessages([
                'email' => ['Too many requests. Please try again in 10 minutes.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['We could not find a user with that email address.'],
            ]);
        }

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Save OTP to user (expires in 10 minutes)
        $user->password_reset_otp = $otp;
        $user->password_reset_otp_expires_at = now()->addMinutes(10);
        $user->save();

        // Queue email instead of sending synchronously (much faster!)
        Mail::to($user->email)->queue(new PasswordResetOtpMail($otp, $user->first_name));

        // Increment rate limit counter
        Cache::put($rateLimitKey, $attempts + 1, now()->addMinutes(10));

        return response()->json([
            'message' => 'OTP has been sent to your email address.',
            'email' => $user->email
        ]);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        if ($user->password_reset_otp !== $request->otp) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP code is invalid.'],
            ]);
        }

        if (now()->isAfter($user->password_reset_otp_expires_at)) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP code has expired. Please request a new one.'],
            ]);
        }

        return response()->json([
            'message' => 'OTP verified successfully.',
            'verified' => true
        ]);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request): JsonResponse
    {
        return $this->store($request);
    }
}