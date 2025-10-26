<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        // Verify OTP one more time
        if ($user->password_reset_otp !== $request->otp) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP code is invalid.'],
            ]);
        }

        if (now()->isAfter($user->password_reset_otp_expires_at)) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP code has expired.'],
            ]);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->password_reset_otp = null;
        $user->password_reset_otp_expires_at = null;
        $user->setRememberToken(Str::random(60));
        $user->save();

        event(new PasswordReset($user));

        return response()->json([
            'message' => 'Your password has been reset successfully.'
        ]);
    }
}