<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Journal;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get current month stats
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $sessionsThisMonth = Appointment::where('student_id', $user->id)
            ->whereMonth('appointment_date', $currentMonth)
            ->whereYear('appointment_date', $currentYear)
            ->whereIn('status', ['approved', 'completed'])
            ->count();
        
        // Get weekly sessions (last 7 days)
        $weeklySessions = Appointment::where('student_id', $user->id)
            ->where('appointment_date', '>=', Carbon::now()->subDays(7))
            ->whereIn('status', ['approved', 'completed'])
            ->count();
        
        // Get all upcoming and recent appointments with therapist info
        $appointments = Appointment::where('student_id', $user->id)
            ->with('therapist:id,first_name,middle_initial,last_name')
            ->whereIn('status', ['pending', 'approved'])
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
                    'therapist' => $appointment->therapist->full_name,
                    'type' => $appointment->appointment_type,
                    'status' => $appointment->status,
                ];
            });
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'first_name' => $user->first_name,
                'email' => $user->email,
                'program' => $user->program,
                'image' => $user->image,
            ],
            'stats' => [
                'sessions_this_month' => $sessionsThisMonth,
                'weekly_sessions' => $weeklySessions,
            ],
            'appointments' => $appointments,
        ]);
    }

    public function getJournal(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();
        
        $journal = Journal::where('user_id', $user->id)
            ->where('journal_date', $today)
            ->first();
        
        return response()->json([
            'content' => $journal ? $journal->content : '',
            'date' => $today,
        ]);
    }

public function saveJournal(Request $request)
{
    $request->validate([
        'content' => 'nullable|string',
    ]);
    
    $user = $request->user();
    $today = Carbon::today()->toDateString();
    
    $content = $request->input('content', '') ?: '';
    
    $journal = Journal::updateOrCreate(
        [
            'user_id' => $user->id,
            'journal_date' => $today,
        ],
        [
            'content' => $content,
        ]
    );
    
    return response()->json([
        'message' => 'Journal saved successfully',
        'journal' => $journal,
    ]);
}
}