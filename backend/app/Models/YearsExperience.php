<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearsExperience extends Model
{
    use HasFactory;

    protected $table = 'years_experience';

    protected $fillable = [
        'therapist_id',
        'years_of_experience',
    ];

    /**
     * Get the therapist that owns the experience record
     */
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
}