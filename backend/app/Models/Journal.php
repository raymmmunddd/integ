<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'journal_date',
    ];

    protected $casts = [
        'journal_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}