<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'middle_initial',
        'last_name',
        'program',
        'email',
        'date_of_birth',
        'gender',
        'house_number',
        'barangay',
        'city_municipality',
        'phone_number',
        'image',
        'password',
        'role',
        'bio',
        'email_verified_at',
        'otp',
        'otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'otp_expires_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Add full name accessor
    public function getFullNameAttribute()
    {
        $middle = $this->middle_initial ? $this->middle_initial . '. ' : '';
        return "{$this->first_name} {$middle}{$this->last_name}";
    }

    // Appointments as a student
    public function studentAppointments()
    {
        return $this->hasMany(Appointment::class, 'student_id');
    }

    // Appointments as a therapist
    public function therapistAppointments()
    {
        return $this->hasMany(Appointment::class, 'therapist_id');
    }

    // Specializations (for therapists)
    public function specializations()
    {
        return $this->belongsToMany(Specialization::class, 'therapist_specialization', 'therapist_id', 'specialization_id');
    }

    // Availabilities (for therapists)
    public function availabilities()
    {
        return $this->hasMany(TherapistAvailability::class, 'therapist_id');
    }

    // Years of experience relationship (therapist)
    public function yearsExperience()
    {
        return $this->hasOne(YearsExperience::class, 'therapist_id');
    }

    // Journals
    public function journals()
    {
        return $this->hasMany(Journal::class);
    }

    // Scope: Only therapists
    public function scopeTherapists($query)
    {
        return $query->where('role', 'therapist');
    }

    // Scope: Only students
    public function scopeStudents($query)
    {
        return $query->where('role', 'student');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'student_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'user_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'user_id');
    }

    public function isTherapist()
    {
        return $this->role === 'therapist';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function sentNotifications()
    {
        return $this->hasMany(Notification::class, 'sender_id');
    }
}