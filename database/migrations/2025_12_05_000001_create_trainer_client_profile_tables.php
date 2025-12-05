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
        // 1. Client Weight Logs
        Schema::create('client_weight_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('weight', 8, 2);
            $table->enum('unit', ['lbs', 'kg']);
            $table->date('logged_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. User Health Profiles
        Schema::create('user_health_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('allergies')->nullable(); // Storing as JSON array
            $table->json('chronic_conditions')->nullable(); // Storing as JSON array
            $table->string('fitness_level')->nullable();
            $table->timestamps();
        });

        // 3. Trainer Client Notes
        Schema::create('trainer_client_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->text('note');
            $table->timestamps();
        });

        // 4. Client Activity Logs (for non-program cardio/activities)
        Schema::create('client_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('activity_type', ['running', 'swimming', 'cycling', 'other']);
            $table->integer('duration_seconds')->nullable();
            $table->decimal('distance_meters', 10, 2)->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_activity_logs');
        Schema::dropIfExists('trainer_client_notes');
        Schema::dropIfExists('user_health_profiles');
        Schema::dropIfExists('client_weight_logs');
    }
};
