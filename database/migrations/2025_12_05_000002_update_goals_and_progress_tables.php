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
        // Update goals table
        Schema::table('goals', function (Blueprint $table) {
            $table->decimal('target_value', 10, 2)->nullable()->after('name');
            $table->decimal('current_value', 10, 2)->nullable()->after('target_value');
            $table->string('metric_unit')->nullable()->after('current_value'); // e.g., 'lbs', 'km', 'kg'
            $table->date('deadline')->nullable()->after('metric_unit');
            $table->timestamp('achieved_at')->nullable()->after('deadline');
        });

        // Update client_progress table
        Schema::table('client_progress', function (Blueprint $table) {
            $table->integer('logged_duration_seconds')->nullable()->after('notes');
            $table->decimal('logged_distance_meters', 10, 2)->nullable()->after('logged_duration_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_progress', function (Blueprint $table) {
            $table->dropColumn(['logged_duration_seconds', 'logged_distance_meters']);
        });

        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn(['target_value', 'current_value', 'metric_unit', 'deadline', 'achieved_at']);
        });
    }
};
