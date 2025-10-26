<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Carbon\Carbon;
use App\Mail\OtpMail;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     * Sends OTP to email instead of immediately registering.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string', 'max:1'],
            'last_name' => ['required', 'string', 'max:255'],
            'program' => ['required', 'string', 'in:BSIT,BSCS,BSEMC'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'string'],
            'barangay' => ['required', 'string'],
            'city_municipality' => ['required', 'string'],
            'phone_number' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store registration data temporarily in session
        session([
            'registration_data' => $request->except(['password_confirmation']),
            'registration_otp' => $otp,
            'registration_otp_expires' => Carbon::now()->addMinutes(10),
        ]);

        // Send OTP email asynchronously using queue
        $userName = $request->first_name . ' ' . $request->last_name;
        
        // Use queue for async email sending - much faster response
        Mail::to($request->email)->queue(new OtpMail($otp, $userName));

        return response()->json([
            'message' => 'OTP sent to your email. Please verify to complete registration.',
        ], 200);
    }

    /**
     * Verify OTP and complete registration
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $registrationData = session('registration_data');
        $storedOtp = session('registration_otp');
        $otpExpires = session('registration_otp_expires');

        // Validate session data exists
        if (!$registrationData || !$storedOtp || !$otpExpires) {
            return response()->json([
                'message' => 'Registration session expired. Please start over.',
            ], 400);
        }

        // Validate email matches
        if ($registrationData['email'] !== $request->email) {
            return response()->json([
                'message' => 'Email does not match registration.',
            ], 400);
        }

        // Validate OTP
        if ($storedOtp !== $request->otp) {
            return response()->json([
                'message' => 'Invalid OTP code.',
            ], 400);
        }

        // Validate OTP not expired
        if (Carbon::now()->isAfter($otpExpires)) {
            return response()->json([
                'message' => 'OTP has expired. Please request a new one.',
            ], 400);
        }

        // Create user
        $user = User::create([
            'first_name' => $registrationData['first_name'],
            'middle_initial' => $registrationData['middle_initial'],
            'last_name' => $registrationData['last_name'],
            'program' => $registrationData['program'],
            'email' => $registrationData['email'],
            'date_of_birth' => $registrationData['date_of_birth'],
            'gender' => $registrationData['gender'],
            'barangay' => $registrationData['barangay'],
            'city_municipality' => $registrationData['city_municipality'],
            'phone_number' => $registrationData['phone_number'],
            'password' => Hash::make($registrationData['password']),
            'role' => 'student',
            'email_verified_at' => Carbon::now(),
        ]);

        // Clear session data
        session()->forget(['registration_data', 'registration_otp', 'registration_otp_expires']);

        return response()->json([
            'message' => 'Registration successful! You can now login.',
            'user' => $user,
        ], 201);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $registrationData = session('registration_data');

        // Validate session data exists
        if (!$registrationData) {
            return response()->json([
                'message' => 'Registration session expired. Please start over.',
            ], 400);
        }

        // Validate email matches
        if ($registrationData['email'] !== $request->email) {
            return response()->json([
                'message' => 'Email does not match registration.',
            ], 400);
        }

        // Generate new OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Update session
        session([
            'registration_otp' => $otp,
            'registration_otp_expires' => Carbon::now()->addMinutes(10),
        ]);

        // Send OTP email asynchronously
        $userName = $registrationData['first_name'] . ' ' . $registrationData['last_name'];
        Mail::to($request->email)->queue(new OtpMail($otp, $userName));

        return response()->json([
            'message' => 'New OTP sent to your email.',
        ], 200);
    }
}