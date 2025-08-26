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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('driver_id')->unique(); // F1 API driverId
            $table->string('name'); // F1 API name
            $table->string('surname'); // F1 API surname
            $table->string('nationality')->nullable(); // F1 API nationality
            $table->string('url')->nullable(); // F1 API url
            $table->string('driver_number')->nullable();
            $table->text('description')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('helmet_url')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('website')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->integer('world_championships')->default(0);
            $table->integer('race_wins')->default(0);
            $table->integer('podiums')->default(0);
            $table->integer('pole_positions')->default(0);
            $table->integer('fastest_laps')->default(0);
            $table->integer('points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['driver_id']);
            $table->index(['name', 'surname']);
            $table->index(['driver_number']);
            $table->index(['is_active']);
            
            // Add foreign key relationship to teams table
            $table->foreignId('team_id')->nullable()->constrained('teams', 'id')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
