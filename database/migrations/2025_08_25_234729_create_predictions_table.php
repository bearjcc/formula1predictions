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
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'race', 'preseason', 'midseason'
            $table->integer('season');
            $table->integer('race_round')->nullable(); // For race predictions
            $table->string('race_id')->nullable(); // For race predictions
            $table->json('prediction_data'); // Store prediction details as JSON
            $table->integer('score')->default(0);
            $table->decimal('accuracy', 5, 2)->nullable(); // Percentage accuracy
            $table->string('status')->default('draft'); // draft, submitted, locked, scored
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('scored_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'season']);
            $table->index(['type', 'season']);
            $table->index(['race_round', 'season']);
            $table->index(['status']);
            $table->unique(['user_id', 'type', 'season', 'race_round']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
