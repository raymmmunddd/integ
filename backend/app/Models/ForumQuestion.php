<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who asked the question
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all answers for this question
     */
    public function answers()
    {
        return $this->hasMany(ForumAnswer::class, 'question_id');
    }

    /**
     * Update status based on answers
     */
    public function updateStatus()
    {
        $this->status = $this->answers()->count() > 0 ? 'answered' : 'pending';
        $this->save();
    }
}