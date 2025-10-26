<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'therapist_id',
        'date',
        'file_path',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the student who owns this note
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the therapist who created this note
     */
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
}