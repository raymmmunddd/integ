<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Get user profile with appointment count
     */
    public function show()
    {
        $user = Auth::user();
        
        $appointmentCount = $user->appointments()->count();

        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'middle_initial' => $user->middle_initial,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'image' => $user->image,
            'gender' => $user->gender,
            'date_of_birth' => $user->date_of_birth,
            'phone_number' => $user->phone_number,
            'house_number' => $user->house_number,
            'barangay' => $user->barangay,
            'city_municipality' => $user->city_municipality,
            'program' => $user->program,
            'appointment_count' => $appointmentCount,
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'middle_initial' => 'sometimes|nullable|string|max:10',
            'last_name' => 'sometimes|string|max:255',
            'gender' => 'sometimes|string|max:50',
            'phone_number' => 'sometimes|string|max:20',
            'house_number' => 'sometimes|nullable|string|max:100',
            'barangay' => 'sometimes|string|max:100',
            'city_municipality' => 'sometimes|string|max:100',
            'date_of_birth' => 'sometimes|date',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Update profile image
     */
    public function updateImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Delete old image if exists
        if ($user->image && Storage::exists($user->image)) {
            Storage::delete($user->image);
        }

        // Store new image
        $path = $request->file('image')->store('profile_images', 'public');

        $user->update(['image' => $path]);

        return response()->json([
            'message' => 'Profile image updated successfully',
            'image_url' => Storage::url($path)
        ]);
    }
}