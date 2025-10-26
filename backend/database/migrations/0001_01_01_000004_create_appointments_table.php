<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade'); 
            $table->foreignId('therapist_id')->constrained('users')->onDelete('cascade'); 
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('treatment_session_type');
            $table->enum('appointment_type', ['online', 'physical']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
