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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('team_id')->unique(); // F1 API teamId
            $table->string('team_name'); // F1 API teamName
            $table->string('nationality')->nullable(); // F1 API nationality
            $table->string('url')->nullable(); // F1 API url
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('website')->nullable();
            $table->string('team_principal')->nullable();
            $table->string('technical_director')->nullable();
            $table->string('chassis')->nullable();
            $table->string('power_unit')->nullable();
            $table->string('base_location')->nullable();
            $table->year('founded')->nullable();
            $table->integer('world_championships')->default(0);
            $table->integer('race_wins')->default(0);
            $table->integer('podiums')->default(0);
            $table->integer('pole_positions')->default(0);
            $table->integer('fastest_laps')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['team_id']);
            $table->index(['team_name']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
