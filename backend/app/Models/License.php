<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    protected $table = 'licenses';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'therapist_id',
        'license_number',
    ];

    /**
     * Get the therapist (user) that owns this license.
     */
    public function therapist()
    {
        return $this->belongsTo(User::class, 'therapist_id');
    }
}
