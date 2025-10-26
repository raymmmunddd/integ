<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MedicalRecordController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = MedicalRecord::query()->with('therapist:id,first_name,middle_initial,last_name');
            
            if ($request->has('student_id') && $request->student_id) {
                $query->where('student_id', $request->student_id);
            } else {
                $query->where('student_id', Auth::id());
            }
            
            $records = $query->orderBy('date', 'desc')
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'file_name' => $record->file_name ?? ($record->symptoms . ' - ' . $record->specialist),
                        'date' => $record->date->format('m/d/Y'),
                        'symptoms' => $record->symptoms,
                        'specialist' => $record->specialist,
                        'therapist_name' => $record->therapist->full_name ?? 'N/A',
                        'file_path' => $record->file_path,
                    ];
                });

            return response()->json($records);
        } catch (\Exception $e) {
            \Log::error('Error fetching medical records: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch medical records', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'record_file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:10240',
            'date' => 'required|date',
            'symptoms' => 'required|string|max:255',
            'specialist' => 'required|string|max:255',
            'student_id' => 'required|exists:users,id',
        ], [
            'record_file.mimes' => 'Only PDF, Word (DOC/DOCX), and TXT files are allowed.',
        ]);

        $path = null;
        $fileName = null;
        
        if ($request->hasFile('record_file')) {
            $file = $request->file('record_file');
            $path = $file->store('medical_records', 'public');
            $fileName = $file->getClientOriginalName();
        }

        $record = MedicalRecord::create([
            'student_id' => $request->student_id,
            'therapist_id' => Auth::id(),
            'date' => $request->date,
            'symptoms' => $request->symptoms,
            'specialist' => $request->specialist,
            'file_path' => $path,
            'file_name' => $fileName,
        ]);

        return response()->json([
            'message' => 'Medical record uploaded successfully',
            'record' => [
                'id' => $record->id,
                'file_name' => $fileName ?? ($record->symptoms . ' - ' . $record->specialist),
                'date' => $record->date->format('m/d/Y'),
                'symptoms' => $record->symptoms,
                'specialist' => $record->specialist,
                'file_path' => $record->file_path,
            ]
        ], 201);
    }

    public function download($id)
    {
        $record = MedicalRecord::findOrFail($id);
        
        if ($record->student_id !== Auth::id() && $record->therapist_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$record->file_path || !Storage::disk('public')->exists($record->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $filePath = Storage::disk('public')->path($record->file_path);
        $fileName = $record->file_name ?? ($record->symptoms . ' - ' . $record->specialist . '.' . pathinfo($record->file_path, PATHINFO_EXTENSION));
        
        return response()->download($filePath, $fileName);
    }

    public function destroy($id)
    {
        $record = MedicalRecord::findOrFail($id);
        
        if ($record->student_id !== Auth::id() && $record->therapist_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($record->file_path && Storage::disk('public')->exists($record->file_path)) {
            Storage::disk('public')->delete($record->file_path);
        }

        $record->delete();

        return response()->json(['message' => 'Medical record deleted successfully']);
    }
}