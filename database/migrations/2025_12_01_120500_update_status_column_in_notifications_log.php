<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change status column to string to accommodate 'read', 'unread', etc.
        DB::statement("ALTER TABLE notifications_log MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to enum
        DB::statement("ALTER TABLE notifications_log MODIFY COLUMN status ENUM('pending', 'sent', 'failed') DEFAULT 'pending'");
    }
};
