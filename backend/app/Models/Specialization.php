<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function therapists()
    {
        return $this->belongsToMany(User::class, 'therapist_specialization', 'specialization_id', 'therapist_id');
    }
}