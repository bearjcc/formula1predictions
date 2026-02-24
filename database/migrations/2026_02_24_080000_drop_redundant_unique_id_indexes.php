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
        // Remove redundant non-unique indexes on columns that already have unique constraints.
        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                // drivers.driver_id already has a unique index from ->unique()
                $table->dropIndex(['driver_id']);
            });
        }

        if (Schema::hasTable('teams')) {
            Schema::table('teams', function (Blueprint $table) {
                // teams.team_id already has a unique index from ->unique()
                $table->dropIndex(['team_id']);
            });
        }

        if (Schema::hasTable('countries')) {
            Schema::table('countries', function (Blueprint $table) {
                // countries.code already has a unique index from ->unique()
                $table->dropIndex(['code']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->index(['driver_id']);
            });
        }

        if (Schema::hasTable('teams')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->index(['team_id']);
            });
        }

        if (Schema::hasTable('countries')) {
            Schema::table('countries', function (Blueprint $table) {
                $table->index(['code']);
            });
        }
    }
};

