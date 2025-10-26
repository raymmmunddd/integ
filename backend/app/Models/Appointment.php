<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'therapist_id',
        'appointment_date',
        'start_time',
        'end_time',
        'treatment_session_type',
        'appointment_type',
        'status',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
    
    public function evaluation()
    {
        return $this->hasOne(Evaluation::class);
    }
}
