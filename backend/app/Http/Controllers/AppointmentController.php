<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function getTherapists(Request $request)
    {
        $query = User::therapists()
            ->with(['specializations', 'availabilities'])
            ->select('id', 'first_name', 'middle_initial', 'last_name', 'image');

        if ($request->has('specialization') && $request->specialization) {
            $query->whereHas('specializations', function ($q) use ($request) {
                $q->where('name', $request->specialization);
            });
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $therapists = $query->get()->map(function ($therapist) {
            $appointment = Appointment::where('student_id', Auth::id())
                ->where('therapist_id', $therapist->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            return [
                'id' => $therapist->id,
                'name' => $therapist->full_name,
                'image' => $therapist->image,
                'specialization' => $therapist->specializations->pluck('name')->join(', '),
                'years_of_experience' => $therapist->yearsExperience?->years_of_experience ?? null,
                'phone_number' => $therapist->phone_number,
                'hasAppointment' => (bool) $appointment,
                'appointmentData' => $appointment ? [
                    'id' => $appointment->id,
                    'date' => $appointment->appointment_date->format('Y-m-d'),
                    'start_time' => $appointment->start_time,
                    'end_time' => $appointment->end_time,
                    'treatment' => $appointment->treatment_session_type,
                    'type' => $appointment->appointment_type,
                    'status' => $appointment->status,
                ] : null,
                'availableDays' => $therapist->availabilities->pluck('day_of_week')->unique()->values(),
                'availableTimes' => $therapist->availabilities->map(function ($avail) {
                    return [
                        'day' => $avail->day_of_week,
                        'start' => date('H:i', strtotime($avail->start_time)),
                        'end' => date('H:i', strtotime($avail->end_time)),
                    ];
                })->toArray(),
            ];
        });

        return response()->json($therapists);
    }

    public function getSpecializations()
    {
        return response()->json(\App\Models\Specialization::orderBy('name')->get());
    }

    public function getAvailableTimeSlots(Request $request)
    {
        $validated = $request->validate([
            'therapist_id' => 'required|exists:users,id',
            'date' => 'required|date|after:today',
        ]);

        $therapist = User::findOrFail($validated['therapist_id']);
        $date = Carbon::parse($validated['date']);
        $dayOfWeek = $date->format('l');

        $availabilities = $therapist->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->get();

        if ($availabilities->isEmpty()) {
            return response()->json([
                'available' => false,
                'message' => 'Therapist is not available on this day.',
                'timeSlots' => [],
            ]);
        }

        $existingAppointments = Appointment::where('therapist_id', $therapist->id)
            ->where('appointment_date', $validated['date'])
            ->whereIn('status', ['pending', 'approved'])
            ->get(['start_time', 'end_time']);

        $timeSlots = [];
        foreach ($availabilities as $availability) {
            $startTime = Carbon::parse($availability->start_time);
            $endTime = Carbon::parse($availability->end_time);
            $currentSlot = clone $startTime;

            while ($currentSlot < $endTime) {
                $nextSlot = (clone $currentSlot)->addHour();

                if ($nextSlot > $endTime) {
                    $nextSlot = clone $endTime;
                }

                $conflict = $existingAppointments->contains(function ($appt) use ($currentSlot, $nextSlot) {
                    return !(
                        $nextSlot->format('H:i:s') <= $appt->start_time ||
                        $currentSlot->format('H:i:s') >= $appt->end_time
                    );
                });

                if (!$conflict) {
                    $timeSlots[] = [
                        'value' => [
                            'start' => $currentSlot->format('H:i:s'),
                            'end' => $nextSlot->format('H:i:s'),
                        ],
                        'display' => $currentSlot->format('g:i A') . ' - ' . $nextSlot->format('g:i A'),
                    ];
                }

                $currentSlot->addHour();
            }
        }

        return response()->json([
            'available' => !empty($timeSlots),
            'timeSlots' => $timeSlots,
            'dayOfWeek' => $dayOfWeek,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'nullable|exists:users,id',
            'therapist_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date|after:today',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'treatment_session_type' => 'required|string',
            'appointment_type' => 'required|in:online,physical',
        ]);

        $user = Auth::user();
        
        if ($user->role === 'therapist') {
            if (!isset($validated['student_id'])) {
                return response()->json(['message' => 'Student ID is required for therapist'], 422);
            }
            $studentId = $validated['student_id'];
            $therapistId = $user->id;
        } else {
            $studentId = $user->id;
            $therapistId = $validated['therapist_id'];
            
            // Check for existing appointment (only for students)
            $existingAppointment = Appointment::where('student_id', $studentId)
                ->where('therapist_id', $therapistId)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existingAppointment) {
                return response()->json(['message' => 'An active appointment already exists with this therapist.'], 422);
            }
        }

        $therapist = User::findOrFail($therapistId);
        $dayOfWeek = Carbon::parse($validated['appointment_date'])->format('l');

        $isAvailable = $therapist->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->whereTime('start_time', '<=', $validated['start_time'])
            ->whereTime('end_time', '>=', $validated['end_time'])
            ->exists();

        if (!$isAvailable) {
            return response()->json([
                'message' => 'The selected time is not available for this therapist on ' . $dayOfWeek . '.',
            ], 422);
        }

        $conflict = Appointment::where('therapist_id', $therapistId)
            ->where('appointment_date', $validated['appointment_date'])
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'This time slot is already booked. Please select another.'], 422);
        }

        $appointment = Appointment::create([
            'student_id' => $studentId,
            'therapist_id' => $therapistId,
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'treatment_session_type' => $validated['treatment_session_type'],
            'appointment_type' => $validated['appointment_type'],
            'status' => 'pending',
        ]);

        $displayTime = $this->formatTimeForDisplay($validated['start_time'], $validated['end_time']);
        $dateDisplay = Carbon::parse($validated['appointment_date'])->format('F j, Y');

        $student = User::find($studentId);
        
        Notification::create([
            'user_id' => $studentId,
            'sender_id' => $therapistId,
            'appointment_id' => $appointment->id,
            'type' => 'appointment_created',
            'message' => "You have an appointment with {$therapist->full_name} on {$dateDisplay} at {$displayTime}.",
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $therapistId,
            'sender_id' => $studentId,
            'appointment_id' => $appointment->id,
            'type' => 'appointment_created',
            'message' => "{$student->full_name} has an appointment on {$dateDisplay} at {$displayTime}.",
            'is_read' => false,
        ]);

        return response()->json(['message' => 'Appointment created successfully!', 'appointment' => $appointment], 201);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        $appointment = Appointment::where('id', $id)
            ->where(function($query) use ($user) {
                $query->where('student_id', $user->id)
                      ->orWhere('therapist_id', $user->id);
            })
            ->firstOrFail();

        $validated = $request->validate([
            'appointment_date' => 'required|date|after:today',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'treatment_session_type' => 'required|string',
            'appointment_type' => 'required|in:online,physical',
        ]);

        $therapist = $appointment->therapist;
        $dayOfWeek = Carbon::parse($validated['appointment_date'])->format('l');

        $isAvailable = $therapist->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->whereTime('start_time', '<=', $validated['start_time'])
            ->whereTime('end_time', '>=', $validated['end_time'])
            ->exists();

        if (!$isAvailable) {
            return response()->json(['message' => 'Selected time not available.'], 422);
        }

        $conflict = Appointment::where('therapist_id', $therapist->id)
            ->where('appointment_date', $validated['appointment_date'])
            ->where('id', '!=', $id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'Time slot already booked.'], 422);
        }

        $appointment->update($validated);

        $displayTime = $this->formatTimeForDisplay($validated['start_time'], $validated['end_time']);
        $dateDisplay = Carbon::parse($validated['appointment_date'])->format('F j, Y');
        
        $recipientId = $user->role === 'therapist' ? $appointment->student_id : $appointment->therapist_id;
        
        Notification::create([
            'user_id' => $recipientId,
            'sender_id' => $user->id,
            'appointment_id' => $appointment->id,
            'type' => 'appointment_updated',
            'message' => 'Appointment updated to ' . $dateDisplay . ' at ' . $displayTime . '.',
            'is_read' => false,
        ]);

        return response()->json(['message' => 'Appointment updated successfully!', 'appointment' => $appointment]);
    }

    public function cancel($id)
    {
        $appointment = Appointment::where('id', $id)
            ->where('student_id', Auth::id())
            ->firstOrFail();

        $appointment->update(['status' => 'cancelled']);

        Notification::create([
            'user_id' => $appointment->therapist_id,
            'sender_id' => Auth::id(),
            'appointment_id' => $appointment->id,
            'type' => 'appointment_cancelled',
            'message' => Auth::user()->full_name . ' cancelled their appointment on ' . Carbon::parse($appointment->appointment_date)->format('F j, Y') . '.',
            'is_read' => false,
        ]);

        return response()->json(['message' => 'Appointment cancelled successfully!']);
    }

    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        $user = Auth::user();
        
        if ($appointment->student_id !== $user->id && 
            $appointment->therapist_id !== $user->id && 
            !$user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $appointment->delete();

        return response()->json(['message' => 'Appointment deleted successfully']);
    }

    public function myAppointments()
    {
        $studentId = auth()->id();
        $now = Carbon::now();
        
        $this->updateAppointmentStatuses($studentId);
        
        $appointments = Appointment::where('student_id', $studentId)
            ->with('therapist')
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json($appointments);
    }

    private function updateAppointmentStatuses($studentId)
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();
        
        Appointment::where('student_id', $studentId)
            ->where('status', 'pending')
            ->where(function($query) use ($today, $currentTime) {
                $query->where('appointment_date', '<', $today)
                    ->orWhere(function($q) use ($today, $currentTime) {
                        $q->where('appointment_date', '=', $today)
                          ->where('start_time', '<=', $currentTime);
                    });
            })
            ->update(['status' => 'approved']);
        
        Appointment::where('student_id', $studentId)
            ->where('status', 'approved')
            ->where(function($query) use ($today, $currentTime) {
                $query->where('appointment_date', '<', $today)
                    ->orWhere(function($q) use ($today, $currentTime) {
                        $q->where('appointment_date', '=', $today)
                          ->where('end_time', '<', $currentTime);
                    });
            })
            ->update(['status' => 'completed']);
    }

    public function approve($id)
    {
        $appointment = Appointment::where('id', $id)
            ->where('therapist_id', Auth::id())
            ->firstOrFail();

        $appointment->update(['status' => 'approved']);

        Notification::create([
            'user_id' => $appointment->student_id,
            'sender_id' => Auth::id(),
            'appointment_id' => $appointment->id,
            'type' => 'appointment_approved',
            'message' => 'Your appointment on ' . Carbon::parse($appointment->appointment_date)->format('F j, Y') . ' has been approved.',
            'is_read' => false,
        ]);

        return response()->json(['message' => 'Appointment approved.', 'appointment' => $appointment]);
    }

    public function reject(Request $request, $id)
    {
        $appointment = Appointment::where('id', $id)
            ->where('therapist_id', Auth::id())
            ->firstOrFail();

        $appointment->update(['status' => 'rejected']);
        $reason = $request->input('reason');
        $message = 'Your appointment on ' . Carbon::parse($appointment->appointment_date)->format('F j, Y') . ' has been rejected.' . ($reason ? ' Reason: ' . $reason : '');

        Notification::create([
            'user_id' => $appointment->student_id,
            'sender_id' => Auth::id(),
            'appointment_id' => $appointment->id,
            'type' => 'appointment_rejected',
            'message' => $message,
            'is_read' => false,
        ]);

        return response()->json(['message' => 'Appointment rejected.', 'appointment' => $appointment]);
    }

    public function complete($id)
    {
        $appointment = Appointment::where('id', $id)
            ->where('therapist_id', Auth::id())
            ->firstOrFail();

        $appointment->update(['status' => 'completed']);

        Notification::create([
            'user_id' => $appointment->student_id,
            'sender_id' => Auth::id(),
            'appointment_id' => $appointment->id,
            'type' => 'appointment_completed',
            'message' => 'Your appointment on ' . Carbon::parse($appointment->appointment_date)->format('F j, Y') . ' has been marked as completed.',
            'is_read' => false,
        ]);

        return response()->json(['message' => 'Appointment marked as completed.', 'appointment' => $appointment]);
    }

    private function formatTimeForDisplay($start, $end)
    {
        return Carbon::parse($start)->format('g:i A') . ' - ' . Carbon::parse($end)->format('g:i A');
    }

    public function therapistDashboard()
    {
        $therapistId = Auth::id();
        
        $appointments = Appointment::where('therapist_id', $therapistId)
            ->with(['student' => function($query) {
                $query->select('id', 'first_name', 'middle_initial', 'last_name', 'phone_number', 'date_of_birth', 'gender', 'house_number', 'barangay', 'city_municipality', 'image');
            }])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(function ($appointment) {
                $student = $appointment->student;
                $dateOfBirth = \Carbon\Carbon::parse($student->date_of_birth ?? null);
                
                return [
                    'id' => $appointment->id,
                    'title' => ucfirst($appointment->treatment_session_type) . ' Session',
                    'patient' => $student->full_name,
                    'patient_contact' => $student->phone_number,
                    'date' => $appointment->appointment_date->format('Y-m-d'),
                    'time' => $this->formatTimeForDisplay($appointment->start_time, $appointment->end_time),
                    'status' => $appointment->status,
                    'appointment_type' => $appointment->appointment_type,
                    'treatment_type' => $appointment->treatment_session_type,
                    'patient_details' => [
                        'name' => $student->full_name,
                        'number' => $student->phone_number,
                        'dob' => $dateOfBirth->isValid() ? $dateOfBirth->format('m/d/Y') : 'N/A',
                        'gender' => $student->gender ?? 'Not specified',
                        'age' => $dateOfBirth->isValid() ? $dateOfBirth->age : null,
                        'house' => $student->house_number ?? 'N/A',
                        'barangay' => $student->barangay ?? 'N/A',
                        'city' => $student->city_municipality ?? 'N/A',
                        'image' => $student->image,
                    ],
                ];
            });

        $evaluations = \App\Models\Evaluation::whereHas('appointment', function($query) use ($therapistId) {
            $query->where('therapist_id', $therapistId);
        })->get();

        $avgRatings = [
            'quality_of_service' => round($evaluations->avg('quality_of_service'), 1),
            'responsiveness' => round($evaluations->avg('responsiveness'), 1),
            'effectiveness' => round($evaluations->avg('effectiveness'), 1),
            'organization' => round($evaluations->avg('organization'), 1),
            'recommendation' => round($evaluations->avg('recommendation'), 1),
        ];

        return response()->json([
            'appointments' => $appointments,
            'average_ratings' => $avgRatings,
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();
        
        $appointment = Appointment::where('id', $id)
            ->where(function($query) use ($user) {
                $query->where('student_id', $user->id)
                      ->orWhere('therapist_id', $user->id);
            })
            ->with(['therapist.specializations', 'therapist.availabilities'])
            ->firstOrFail();

        return response()->json([
            'id' => $appointment->id,
            'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
            'start_time' => $appointment->start_time,
            'end_time' => $appointment->end_time,
            'treatment_session_type' => $appointment->treatment_session_type,
            'appointment_type' => $appointment->appointment_type,
            'status' => $appointment->status,
            'therapist' => [
                'id' => $appointment->therapist->id,
                'first_name' => $appointment->therapist->first_name,
                'last_name' => $appointment->therapist->last_name,
                'image' => $appointment->therapist->image,
                'specializations' => $appointment->therapist->specializations,
                'availabilities' => $appointment->therapist->availabilities->map(function($avail) {
                    return [
                        'day_of_week' => $avail->day_of_week,
                        'start_time' => $avail->start_time,
                        'end_time' => $avail->end_time,
                    ];
                }),
            ],
        ]);
    }

    public function getAppointmentDetails($id)
    {
        try {
            $appointment = Appointment::where('id', $id)
                ->where('therapist_id', Auth::id())
                ->with(['student', 'evaluation'])
                ->firstOrFail();

            $student = $appointment->student;
            
            $dateOfBirth = null;
            $age = null;
            $dobDisplay = 'N/A';
            
            if ($student->date_of_birth) {
                try {
                    $dateOfBirth = \Carbon\Carbon::parse($student->date_of_birth);
                    $age = $dateOfBirth->age;
                    $dobDisplay = $dateOfBirth->format('m/d/Y');
                } catch (\Exception $e) {
                    // Invalid date
                }
            }
            
            return response()->json([
                'appointment' => [
                    'id' => $appointment->id,
                    'title' => ucfirst($appointment->treatment_session_type ?? 'consultation') . ' Session',
                    'date' => $appointment->appointment_date->format('Y-m-d'),
                    'time' => $this->formatTimeForDisplay($appointment->start_time, $appointment->end_time),
                    'status' => $appointment->status ?? 'pending',
                    'appointment_type' => $appointment->appointment_type ?? 'physical',
                    'treatment_type' => $appointment->treatment_session_type ?? 'consultation',
                ],
                'patient' => [
                    'id' => $student->id,
                    'name' => $student->full_name ?? 'Unknown',
                    'number' => $student->phone_number ?? 'N/A',
                    'dob' => $dobDisplay,
                    'gender' => $student->gender ?? 'Not specified',
                    'age' => $age,
                    'house' => $student->house_number ?? 'N/A',
                    'barangay' => $student->barangay ?? 'N/A',
                    'city' => $student->city_municipality ?? 'N/A',
                    'image' => $student->image,
                ],
                'has_evaluation' => $appointment->evaluation !== null,
                'evaluation' => $appointment->evaluation ? [
                    'quality_of_service' => (float) ($appointment->evaluation->quality_of_service ?? 0),
                    'responsiveness' => (float) ($appointment->evaluation->responsiveness ?? 0),
                    'effectiveness' => (float) ($appointment->evaluation->effectiveness ?? 0),
                    'organization' => (float) ($appointment->evaluation->organization ?? 0),
                    'recommendation' => (float) ($appointment->evaluation->recommendation ?? 0),
                    'message' => $appointment->evaluation->message ?? null,
                ] : null,
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Appointment not found or you do not have permission to view it.'
            ], 404);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching appointment details: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error fetching appointment details. Please try again later.'
            ], 500);
        }
    }

    public function getPatientReflections($studentId)
    {
        try {
            $therapistId = Auth::id();
            
            $evaluations = \App\Models\Evaluation::whereHas('appointment', function($query) use ($studentId, $therapistId) {
                $query->where('student_id', $studentId)
                      ->where('therapist_id', $therapistId)
                      ->where('status', 'completed');
            })
            ->with('appointment')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($evaluation) {
                return [
                    'id' => $evaluation->id,
                    'date' => $evaluation->created_at->format('Y-m-d'),
                    'text' => $evaluation->message ?? 'No reflection provided.',
                    'appointment_date' => $evaluation->appointment->appointment_date->format('F j, Y'),
                ];
            });

            return response()->json($evaluations);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching patient reflections: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching patient reflections.'
            ], 500);
        }
    }
}