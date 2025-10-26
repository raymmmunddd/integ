<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    /**
     * Submit post-appointment evaluation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'quality_of_service' => 'required|integer|min:1|max:5',
            'responsiveness' => 'required|integer|min:1|max:5',
            'effectiveness' => 'required|integer|min:1|max:5',
            'organization' => 'required|integer|min:1|max:5',
            'recommendation' => 'required|integer|min:1|max:5',
            'message' => 'nullable|string|max:1000',
        ]);

        // Verify appointment belongs to student and is completed
        $appointment = Appointment::where('id', $validated['appointment_id'])
            ->where('student_id', Auth::id())
            ->where('status', 'completed')
            ->firstOrFail();

        // Check if evaluation already exists
        $existingEvaluation = Evaluation::where('appointment_id', $appointment->id)->first();
        if ($existingEvaluation) {
            return response()->json([
                'message' => 'Evaluation already submitted for this appointment'
            ], 422);
        }

        $evaluation = Evaluation::create($validated);

        return response()->json([
            'message' => 'Evaluation submitted successfully',
            'evaluation' => $evaluation
        ], 201);
    }

    /**
     * Check if appointment has evaluation
     */
    public function check($appointmentId)
    {
        $hasEvaluation = Evaluation::where('appointment_id', $appointmentId)->exists();

        return response()->json([
            'has_evaluation' => $hasEvaluation
        ]);
    }
}