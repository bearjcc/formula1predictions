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
        Schema::create('races', function (Blueprint $table) {
            $table->id();
            $table->integer('season');
            $table->integer('round');
            $table->string('race_name'); // F1 API raceName
            $table->date('date'); // F1 API date
            $table->time('time')->nullable(); // F1 API time
            $table->string('circuit_api_id')->nullable(); // F1 API circuit.circuitId
            $table->string('circuit_name')->nullable(); // F1 API circuit.circuitName
            $table->string('circuit_url')->nullable(); // F1 API circuit.url
            $table->string('country')->nullable(); // F1 API circuit.country
            $table->string('locality')->nullable(); // F1 API circuit.locality
            $table->decimal('circuit_length', 8, 3)->nullable(); // in kilometers
            $table->integer('laps')->nullable();
            $table->string('weather')->nullable();
            $table->decimal('temperature', 4, 1)->nullable(); // in celsius
            $table->string('status')->default('upcoming'); // upcoming, ongoing, completed, cancelled
            $table->boolean('has_sprint')->default(false);
            $table->boolean('is_special_event')->default(false);
            $table->json('results')->nullable(); // Store race results as JSON
            $table->timestamps();

            $table->index(['season', 'round']);
            $table->index(['season', 'race_name']);
            $table->index(['date']);
            $table->index(['status']);
            $table->index(['circuit_api_id']);

            // Add foreign key relationship to circuits table
            $table->foreignId('circuit_id')->nullable()->constrained('circuits', 'id')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};
