<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('predictions') || ! Schema::hasColumn('predictions', 'accuracy')) {
            return;
        }

        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn('accuracy');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('predictions') || Schema::hasColumn('predictions', 'accuracy')) {
            return;
        }

        Schema::table('predictions', function (Blueprint $table) {
            $table->decimal('accuracy', 5, 2)->nullable()->after('score');
        });
    }
};
