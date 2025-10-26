<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Note::query()->with('therapist:id,first_name,middle_initial,last_name');
            
            if ($request->has('student_id') && $request->student_id) {
                $query->where('student_id', $request->student_id);
            } else {
                $query->where('student_id', Auth::id());
            }
            
            $notes = $query->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($note) {
                    return [
                        'id' => $note->id,
                        'file_name' => $note->file_name ?? ('Note - ' . $note->created_at->format('m/d/Y')),
                        'date' => $note->created_at->format('m/d/Y'),
                        'therapist_name' => $note->therapist->full_name ?? 'N/A',
                        'file_path' => $note->file_path,
                    ];
                });

            return response()->json($notes);
        } catch (\Exception $e) {
            \Log::error('Error fetching notes: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch notes', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'note_file' => 'required|file|mimes:pdf,doc,docx,txt|max:10240',
            'student_id' => 'required|exists:users,id',
        ], [
            'note_file.mimes' => 'Only PDF, Word (DOC/DOCX), and TXT files are allowed.',
        ]);

        $file = $request->file('note_file');
        $path = $file->store('notes', 'public');

        $note = Note::create([
            'student_id' => $request->student_id,
            'therapist_id' => Auth::id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'date' => now(),
        ]);

        return response()->json([
            'message' => 'Note uploaded successfully',
            'note' => [
                'id' => $note->id,
                'file_name' => $note->file_name,
                'date' => $note->created_at->format('m/d/Y'),
                'file_path' => $note->file_path,
            ]
        ], 201);
    }

    public function download($id)
    {
        $note = Note::findOrFail($id);
        
        if ($note->student_id !== Auth::id() && $note->therapist_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$note->file_path || !Storage::disk('public')->exists($note->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $filePath = Storage::disk('public')->path($note->file_path);
        $fileName = $note->file_name ?? ('Note - ' . $note->created_at->format('m-d-Y') . '.' . pathinfo($note->file_path, PATHINFO_EXTENSION));
        
        return response()->download($filePath, $fileName);
    }

    public function destroy($id)
    {
        $note = Note::findOrFail($id);
        
        if ($note->student_id !== Auth::id() && $note->therapist_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($note->file_path && Storage::disk('public')->exists($note->file_path)) {
            Storage::disk('public')->delete($note->file_path);
        }

        $note->delete();

        return response()->json(['message' => 'Note deleted successfully']);
    }
}