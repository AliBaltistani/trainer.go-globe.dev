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
        Schema::table('nutrition_meals', function (Blueprint $table) {
            if (!Schema::hasColumn('nutrition_meals', 'image_url')) {
                $table->string('image_url')->nullable()->after('instructions');
            }

            if (!Schema::hasColumn('nutrition_meals', 'calories_per_serving')) {
                $table->decimal('calories_per_serving', 8, 2)->nullable()->after('servings');
            }

            if (!Schema::hasColumn('nutrition_meals', 'protein_per_serving')) {
                $table->decimal('protein_per_serving', 8, 2)->nullable()->after('calories_per_serving');
            }

            if (!Schema::hasColumn('nutrition_meals', 'carbs_per_serving')) {
                $table->decimal('carbs_per_serving', 8, 2)->nullable()->after('protein_per_serving');
            }

            if (!Schema::hasColumn('nutrition_meals', 'fats_per_serving')) {
                $table->decimal('fats_per_serving', 8, 2)->nullable()->after('carbs_per_serving');
            }
        });

        if (Schema::hasColumn('nutrition_meals', 'media_url')) {
            DB::statement('UPDATE nutrition_meals SET image_url = media_url WHERE image_url IS NULL AND media_url IS NOT NULL');
        }

        if (Schema::hasColumn('nutrition_meals', 'calories')) {
            DB::statement('UPDATE nutrition_meals SET calories_per_serving = calories WHERE calories_per_serving IS NULL');
        }
        if (Schema::hasColumn('nutrition_meals', 'protein')) {
            DB::statement('UPDATE nutrition_meals SET protein_per_serving = protein WHERE protein_per_serving IS NULL');
        }
        if (Schema::hasColumn('nutrition_meals', 'carbs')) {
            DB::statement('UPDATE nutrition_meals SET carbs_per_serving = carbs WHERE carbs_per_serving IS NULL');
        }
        if (Schema::hasColumn('nutrition_meals', 'fats')) {
            DB::statement('UPDATE nutrition_meals SET fats_per_serving = fats WHERE fats_per_serving IS NULL');
        }

        Schema::table('nutrition_meals', function (Blueprint $table) {
            if (Schema::hasColumn('nutrition_meals', 'media_url')) {
                $table->dropColumn('media_url');
            }
            if (Schema::hasColumn('nutrition_meals', 'calories')) {
                $table->dropColumn('calories');
            }
            if (Schema::hasColumn('nutrition_meals', 'protein')) {
                $table->dropColumn('protein');
            }
            if (Schema::hasColumn('nutrition_meals', 'carbs')) {
                $table->dropColumn('carbs');
            }
            if (Schema::hasColumn('nutrition_meals', 'fats')) {
                $table->dropColumn('fats');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutrition_meals', function (Blueprint $table) {
            if (!Schema::hasColumn('nutrition_meals', 'media_url')) {
                $table->string('media_url')->nullable()->after('instructions');
            }
            if (!Schema::hasColumn('nutrition_meals', 'calories')) {
                $table->decimal('calories', 8, 2)->nullable()->after('servings');
            }
            if (!Schema::hasColumn('nutrition_meals', 'protein')) {
                $table->decimal('protein', 8, 2)->nullable()->after('calories');
            }
            if (!Schema::hasColumn('nutrition_meals', 'carbs')) {
                $table->decimal('carbs', 8, 2)->nullable()->after('protein');
            }
            if (!Schema::hasColumn('nutrition_meals', 'fats')) {
                $table->decimal('fats', 8, 2)->nullable()->after('carbs');
            }
        });

        if (Schema::hasColumn('nutrition_meals', 'image_url')) {
            DB::statement('UPDATE nutrition_meals SET media_url = image_url WHERE media_url IS NULL AND image_url IS NOT NULL');
        }
        if (Schema::hasColumn('nutrition_meals', 'calories_per_serving')) {
            DB::statement('UPDATE nutrition_meals SET calories = calories_per_serving WHERE calories IS NULL');
        }
        if (Schema::hasColumn('nutrition_meals', 'protein_per_serving')) {
            DB::statement('UPDATE nutrition_meals SET protein = protein_per_serving WHERE protein IS NULL');
        }
        if (Schema::hasColumn('nutrition_meals', 'carbs_per_serving')) {
            DB::statement('UPDATE nutrition_meals SET carbs = carbs_per_serving WHERE carbs IS NULL');
        }
        if (Schema::hasColumn('nutrition_meals', 'fats_per_serving')) {
            DB::statement('UPDATE nutrition_meals SET fats = fats_per_serving WHERE fats IS NULL');
        }

        Schema::table('nutrition_meals', function (Blueprint $table) {
            if (Schema::hasColumn('nutrition_meals', 'image_url')) {
                $table->dropColumn('image_url');
            }
            if (Schema::hasColumn('nutrition_meals', 'calories_per_serving')) {
                $table->dropColumn('calories_per_serving');
            }
            if (Schema::hasColumn('nutrition_meals', 'protein_per_serving')) {
                $table->dropColumn('protein_per_serving');
            }
            if (Schema::hasColumn('nutrition_meals', 'carbs_per_serving')) {
                $table->dropColumn('carbs_per_serving');
            }
            if (Schema::hasColumn('nutrition_meals', 'fats_per_serving')) {
                $table->dropColumn('fats_per_serving');
            }
        });
    }
};

