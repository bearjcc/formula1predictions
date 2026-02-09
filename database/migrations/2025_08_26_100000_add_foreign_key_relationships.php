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
        // Foreign key for predictions.race_id to races.id was originally
        // introduced here, but existing production data uses a string
        // column for race_id, which is incompatible with the bigint
        // primary key on races.id in MySQL.
        //
        // To keep deploy-time migrations idempotent and avoid crashes on
        // long-lived databases (e.g. Railway), this migration is now a
        // no-op. New schema changes that introduce a proper foreign key
        // should be handled in dedicated, backward-compatible migrations.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: we no longer create the foreign key constraint here.
    }
};
