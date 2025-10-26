<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'quality_of_service',
        'responsiveness',
        'effectiveness',
        'organization',
        'recommendation',
        'message',
    ];

    /**
     * Get the appointment this evaluation belongs to
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}