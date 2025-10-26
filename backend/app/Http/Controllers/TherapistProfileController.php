<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Specialization;
use App\Models\TherapistAvailability;
use App\Models\YearsExperience;
use App\Models\License;

class TherapistProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        if ($user->role !== 'therapist') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $specializations = $user->specializations()->get(['specializations.id', 'specializations.name']);

        $availabilities = TherapistAvailability::where('therapist_id', $user->id)
            ->orderBy('id')
            ->get()
            ->groupBy(function($item) {
                return $item->start_time . '-' . $item->end_time;
            })
            ->map(function($group) {
                $first = $group->first();
                return [
                    'days' => $group->pluck('day_of_week')->toArray(),
                    'start_time' => $first->start_time,
                    'end_time' => $first->end_time
                ];
            })
            ->values();

        $yearsExperience = YearsExperience::where('therapist_id', $user->id)->first();

        $licenses = License::where('therapist_id', $user->id)
            ->pluck('license_number');

        return response()->json([
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'image' => $user->image,
            'phone_number' => $user->phone_number,
            'specializations' => $specializations,
            'years_of_experience' => $yearsExperience?->years_of_experience,
            'licenses' => $licenses,
            'bio' => $user->bio ?? '',
            'availabilities' => $availabilities
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'therapist') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'phone_number' => 'required|string|max:20',
            'specializations' => 'required|array|min:1',
            'specializations.*' => 'exists:specializations,id',
            'years_of_experience' => 'required|string|in:1-5 years,6-10 years,11-15 years,16-20 years,21+ years',
            'licenses' => 'nullable|array',
            'licenses.*' => 'string|max:50',
            'bio' => 'nullable|string|max:1000',
            'availabilities' => 'required|array|min:1',
            'availabilities.*.days' => 'required|array|min:1',
            'availabilities.*.days.*' => 'string|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'availabilities.*.start_time' => 'required|date_format:H:i',
            'availabilities.*.end_time' => 'required|date_format:H:i|after:availabilities.*.start_time',
        ]);

        DB::beginTransaction();
        try {
            $user->update([
                'phone_number' => $validated['phone_number'],
                'bio' => $validated['bio'] ?? null,
            ]);

            $user->specializations()->sync($validated['specializations']);

            YearsExperience::updateOrCreate(
                ['therapist_id' => $user->id],
                ['years_of_experience' => $validated['years_of_experience']]
            );

            License::where('therapist_id', $user->id)->delete();
            if (!empty($validated['licenses'])) {
                foreach ($validated['licenses'] as $licenseNumber) {
                    License::create([
                        'therapist_id' => $user->id,
                        'license_number' => $licenseNumber,
                    ]);
                }
            }

            TherapistAvailability::where('therapist_id', $user->id)->delete();
            foreach ($validated['availabilities'] as $availability) {
                foreach ($availability['days'] as $day) {
                    TherapistAvailability::create([
                        'therapist_id' => $user->id,
                        'day_of_week' => $day,
                        'start_time' => $availability['start_time'],
                        'end_time' => $availability['end_time'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user->fresh()->load('specializations')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
