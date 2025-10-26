<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();

            // Student (the one involved in the record)
            $table->foreignId('student_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Therapist (the one who created the record)
            $table->foreignId('therapist_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Record details
            $table->date('date');
            $table->string('symptoms');
            $table->string('specialist');
            $table->string('file_path')->nullable(); // For uploaded file path (PDF/image)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
