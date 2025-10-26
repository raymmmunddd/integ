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
        Schema::create('years_experience', function (Blueprint $table) {
            $table->id();

            // Link to therapist (user)
            $table->foreignId('therapist_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Example values: "1-5 years", "6-10 years", "10+ years"
            $table->string('years_of_experience', 20);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('years_experience');
    }
};
