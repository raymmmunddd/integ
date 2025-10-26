<?php

namespace App\Http\Controllers;

use App\Models\ForumQuestion;
use App\Models\ForumAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForumController extends Controller
{
    /**
     * Get all forum questions with answers
     * Used by both student and therapist views
     */
    public function index()
    {
        $questions = ForumQuestion::with([
            'answers.therapist:id,first_name,last_name,image',
            'user:id'
        ])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($question) {
            return [
                'id' => $question->id,
                'user_id' => $question->user_id,
                'content' => $question->content,
                'status' => $question->status,
                'created_at' => $question->created_at->toIso8601String(),
                'answers' => $question->answers->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'therapist_id' => $answer->therapist_id,
                        'content' => $answer->content,
                        'created_at' => $answer->created_at->toIso8601String(),
                        'therapist' => [
                            'id' => $answer->therapist->id,
                            'name' => $answer->therapist->first_name . ' ' . $answer->therapist->last_name,
                            'avatar' => $answer->therapist->image ? asset('storage/' . $answer->therapist->image) : null,
                        ]
                    ];
                })
            ];
        });

        return response()->json([
            'questions' => $questions
        ]);
    }

    /**
     * Post a new question (Students only)
     */
    public function storeQuestion(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        // Ensure only students can post questions
        if ($user->role !== 'student') {
            return response()->json([
                'message' => 'Only students can post questions'
            ], 403);
        }

        $question = ForumQuestion::create([
            'user_id' => $user->id,
            'content' => $request->content,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Question posted successfully',
            'question' => $question
        ], 201);
    }

    /**
     * Post an answer to a question (Therapists only)
     */
    public function storeAnswer(Request $request, $questionId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        // Ensure only therapists can post answers
        if ($user->role !== 'therapist') {
            return response()->json([
                'message' => 'Only therapists can post answers'
            ], 403);
        }

        $question = ForumQuestion::findOrFail($questionId);

        $answer = ForumAnswer::create([
            'question_id' => $question->id,
            'therapist_id' => $user->id,
            'content' => $request->content,
        ]);

        // Load the therapist relationship
        $answer->load('therapist:id,first_name,last_name,image');

        return response()->json([
            'message' => 'Answer posted successfully',
            'answer' => [
                'id' => $answer->id,
                'therapist_id' => $answer->therapist_id,
                'content' => $answer->content,
                'created_at' => $answer->created_at->toIso8601String(),
                'therapist' => [
                    'id' => $answer->therapist->id,
                    'name' => $answer->therapist->first_name . ' ' . $answer->therapist->last_name,
                    'avatar' => $answer->therapist->image ? asset('storage/' . $answer->therapist->image) : null,
                ]
            ]
        ], 201);
    }
}