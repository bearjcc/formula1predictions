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
        Schema::create('standings', function (Blueprint $table) {
            $table->id();
            $table->integer('season');
            $table->string('type'); // 'drivers' or 'constructors'
            $table->integer('round')->nullable(); // For race-by-race standings
            $table->string('entity_id'); // driver_id or team_id
            $table->string('entity_name'); // driver name or team name
            $table->integer('position');
            $table->decimal('points', 8, 2)->default(0);
            $table->integer('wins')->default(0);
            $table->integer('podiums')->default(0);
            $table->integer('poles')->default(0);
            $table->integer('fastest_laps')->default(0);
            $table->integer('dnfs')->default(0);
            $table->json('additional_data')->nullable(); // Store any additional API data
            $table->timestamps();

            $table->index(['season', 'type']);
            $table->index(['season', 'type', 'round']);
            $table->index(['entity_id']);
            $table->index(['position']);
            $table->unique(['season', 'type', 'round', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standings');
    }
};
