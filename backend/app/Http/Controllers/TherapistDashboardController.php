<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Carbon\Carbon;

class TherapistDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Ensure user is a therapist
        if ($user->role !== 'therapist') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Get current month stats
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $sessionsThisMonth = Appointment::where('therapist_id', $user->id)
            ->whereMonth('appointment_date', $currentMonth)
            ->whereYear('appointment_date', $currentYear)
            ->whereIn('status', ['approved', 'completed'])
            ->count();
        
        // Get weekly sessions (last 7 days)
        $weeklySessions = Appointment::where('therapist_id', $user->id)
            ->where('appointment_date', '>=', Carbon::now()->subDays(7))
            ->whereIn('status', ['approved', 'completed'])
            ->count();
        
        // Get total unique patients
        $totalPatients = Appointment::where('therapist_id', $user->id)
            ->distinct('student_id')
            ->count('student_id');
        
        // Get total appointments (all statuses)
        $totalAppointments = Appointment::where('therapist_id', $user->id)
            ->count();
        
        // Get all appointments with student info
        $appointments = Appointment::where('therapist_id', $user->id)
            ->with('student:id,first_name,middle_initial,last_name,phone_number,image')
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get()
            ->map(function ($appointment) {
                $startTime = Carbon::parse($appointment->start_time)->format('g:i A');
                $endTime = Carbon::parse($appointment->end_time)->format('g:i A');
                
                return [
                    'id' => $appointment->id,
                    'date' => $appointment->appointment_date->format('Y-m-d'),
                    'time' => "{$startTime} - {$endTime}",
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'title' => $appointment->treatment_session_type,
                    'patient' => $appointment->student->full_name,
                    'type' => $appointment->appointment_type,
                    'status' => $appointment->status,
                    'patient' => $appointment->student->full_name,
                    'patient_number' => $appointment->student->phone_number,
                    'patient_house_no' => $appointment->student->house_number,
                    'patient_barangay' => $appointment->student->barangay,
                    'patient_city_municipality' => $appointment->student->city_municipality,
                    'patient_image' => $appointment->student->image,
                ];
            });
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'image' => $user->image,
            ],
            'stats' => [
                'sessions_this_month' => $sessionsThisMonth,
                'weekly_sessions' => $weeklySessions,
                'total_patients' => $totalPatients,
                'total_appointments' => $totalAppointments,
            ],
            'appointments' => $appointments,
        ]);
    }
}