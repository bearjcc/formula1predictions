<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Used by deploy-time commands that must run only once (e.g. test-year bot predictions).
     */
    public function up(): void
    {
        Schema::create('one_time_jobs', function (Blueprint $table) {
            $table->string('name', 64)->primary();
            $table->timestamp('run_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('one_time_jobs');
    }
};
