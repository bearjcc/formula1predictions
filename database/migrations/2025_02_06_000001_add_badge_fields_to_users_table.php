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
        Schema::table('users', function (Blueprint $table) {
            // Season Supporter badge
            $table->boolean('is_season_supporter')->default(false);
            $table->timestamp('supporter_since')->nullable();
            
            // Badge system - store as JSON for flexibility
            $table->json('badges')->nullable();
            
            // Profile statistics cache (optional, for performance)
            $table->json('stats_cache')->nullable();
            $table->timestamp('stats_cache_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_season_supporter',
                'supporter_since',
                'badges',
                'stats_cache',
                'stats_cache_updated_at'
            ]);
        });
    }
};
