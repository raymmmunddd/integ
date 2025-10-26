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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();

            // Link evaluation to appointment
            $table->foreignId('appointment_id')
                ->constrained('appointments')
                ->onDelete('cascade');

            // Ratings (1 to 5)
            $table->unsignedTinyInteger('quality_of_service')->comment('1-5 rating');
            $table->unsignedTinyInteger('responsiveness')->comment('1-5 rating');
            $table->unsignedTinyInteger('effectiveness')->comment('1-5 rating');
            $table->unsignedTinyInteger('organization')->comment('1-5 rating');
            $table->unsignedTinyInteger('recommendation')->comment('1-5 rating');

            // Optional message
            $table->text('message')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
