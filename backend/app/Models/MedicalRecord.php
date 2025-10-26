<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'therapist_id',
        'date',
        'symptoms',
        'specialist',
        'file_path',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the student who owns this medical record
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the therapist who created this record
     */
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
}