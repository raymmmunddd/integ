<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TherapistAvailability extends Model
{
    use HasFactory;

    protected $table = 'therapist_availabilities';

    protected $fillable = [
        'therapist_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
    ];

    /**
     * Relationship: Availability belongs to a therapist
     */
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute()
    {
        return date('g:i A', strtotime($this->start_time)) . ' - ' . 
               date('g:i A', strtotime($this->end_time));
    }
}