<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable()->index();
            }
            if (!Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable()->index();
            }
        });

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique(['provider', 'provider_id'], 'users_provider_provider_id_unique');
            });
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'provider')) {
                $table->dropIndex(['provider']);
                $table->dropColumn('provider');
            }
            if (Schema::hasColumn('users', 'provider_id')) {
                $table->dropIndex(['provider_id']);
                $table->dropColumn('provider_id');
            }
            try {
                $table->dropUnique('users_provider_provider_id_unique');
            } catch (\Throwable $e) {
            }
        });
    }
};

