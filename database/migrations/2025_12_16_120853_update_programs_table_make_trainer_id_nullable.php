<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Update Programs Table - Make trainer_id nullable
 * 
 * Allows clients to create their own programs without a trainer
 * 
 * @package     Laravel CMS App
 * @subpackage  Migrations
 * @category    Workout Exercise Management
 * @author      Go Globe CMS Team
 * @since       1.0.0
 * @version     1.1.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['trainer_id']);
            
            // Drop the composite index that includes trainer_id
            $table->dropIndex(['trainer_id', 'client_id']);
        });

        // Modify the column to be nullable
        DB::statement('ALTER TABLE `programs` MODIFY `trainer_id` BIGINT UNSIGNED NULL');

        Schema::table('programs', function (Blueprint $table) {
            // Re-add the foreign key constraint with nullable support
            $table->foreign('trainer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            // Re-add indexes (separate indexes since trainer_id can be null)
            $table->index('trainer_id');
            $table->index('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['trainer_id']);
            $table->dropIndex(['client_id']);
            
            // Drop foreign key
            $table->dropForeign(['trainer_id']);
        });

        // Make trainer_id NOT NULL again (only if no null values exist)
        DB::statement('ALTER TABLE `programs` MODIFY `trainer_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('programs', function (Blueprint $table) {
            // Re-add foreign key
            $table->foreign('trainer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            // Re-add composite index
            $table->index(['trainer_id', 'client_id']);
        });
    }
};
