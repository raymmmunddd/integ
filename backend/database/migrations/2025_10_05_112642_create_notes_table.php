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
        Schema::create('notes', function (Blueprint $table) { 
            $table->id();

            // Student (the one involved in the record)
            $table->foreignId('student_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Therapist (the one who created the note)
            $table->foreignId('therapist_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Note details 
            $table->date('date');
            $table->string('file_path')->nullable(); // For uploaded file path (PDF/image)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes'); 
    }
};
