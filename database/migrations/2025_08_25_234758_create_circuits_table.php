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
        Schema::create('circuits', function (Blueprint $table) {
            $table->id();
            $table->string('circuit_id')->unique(); // F1 API circuitId
            $table->string('circuit_name'); // F1 API circuitName
            $table->string('url')->nullable(); // F1 API url
            $table->string('country')->nullable(); // F1 API country
            $table->string('locality')->nullable(); // F1 API locality
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('circuit_length', 8, 3)->nullable(); // in kilometers
            $table->integer('laps')->nullable();
            $table->text('description')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('map_url')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('first_grand_prix')->nullable();
            $table->string('lap_record_driver')->nullable();
            $table->string('lap_record_time')->nullable();
            $table->integer('lap_record_year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['circuit_id']);
            $table->index(['circuit_name']);
            $table->index(['country']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circuits');
    }
};
