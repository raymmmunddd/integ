<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'therapist_id',
        'content',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the question this answer belongs to
     */
    public function question()
    {
        return $this->belongsTo(ForumQuestion::class, 'question_id');
    }

    /**
     * Get the therapist who answered
     */
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }

    /**
     * Boot method to update question status when answer is created
     */
    protected static function booted()
    {
        static::created(function ($answer) {
            $answer->question->updateStatus();
        });

        static::deleted(function ($answer) {
            $answer->question->updateStatus();
        });
    }
}