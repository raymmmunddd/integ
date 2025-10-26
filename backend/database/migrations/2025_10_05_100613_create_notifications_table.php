<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();

                // Receiver of the notification
                $table->foreignId('user_id')
                      ->constrained('users')
                      ->onDelete('cascade');

                // Sender (optional)
                $table->foreignId('sender_id')
                      ->nullable()
                      ->constrained('users')
                      ->onDelete('cascade');

                // Related appointment (optional)
                $table->foreignId('appointment_id')
                      ->nullable()
                      ->constrained('appointments')
                      ->onDelete('cascade');

                // Notification details
                $table->string('type'); // e.g., appointment_created, appointment_approved
                $table->text('message');

                // Read tracking
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();

                $table->timestamps();

                // Indexes for performance
                $table->index(['user_id', 'type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
