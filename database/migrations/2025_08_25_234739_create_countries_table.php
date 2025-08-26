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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique(); // ISO country code
            $table->string('flag_url')->nullable();
            $table->text('description')->nullable();
            $table->integer('f1_races_hosted')->default(0);
            $table->integer('world_championships_won')->default(0);
            $table->integer('drivers_count')->default(0);
            $table->integer('teams_count')->default(0);
            $table->integer('circuits_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['name']);
            $table->index(['code']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
