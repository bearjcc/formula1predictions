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
        if (Schema::hasTable('subscriptions')) {
            Schema::drop('subscriptions');
        }

        if (Schema::hasTable('subscription_items')) {
            Schema::drop('subscription_items');
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $dropColumns = [];

                foreach ([
                    'stripe_id',
                    'pm_type',
                    'pm_last_four',
                    'trial_ends_at',
                    'is_season_supporter',
                    'supporter_since',
                    'badges',
                    'stats_cache',
                    'stats_cache_updated_at',
                ] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $dropColumns[] = $column;
                    }
                }

                if (! empty($dropColumns)) {
                    $table->dropColumn($dropColumns);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: billing and supporter artifacts are permanently removed.
    }
};

