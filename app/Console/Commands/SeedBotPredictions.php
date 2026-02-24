<?php

namespace App\Console\Commands;

use Database\Seeders\BotPredictionsSeeder;
use Database\Seeders\ChampionshipOrderBotSeeder;
use Database\Seeders\LastYearChampionshipOrderBotSeeder;
use Database\Seeders\PreviousCircuitBotSeeder;
use Database\Seeders\PreviousYearChampionshipBotSeeder;
use Database\Seeders\RandomBotSeeder;
use Database\Seeders\SmartWeightedBotSeeder;
use Illuminate\Console\Command;

class SeedBotPredictions extends Command
{
    protected $signature = 'bots:seed
                            {--only= : Comma-separated: last, season, random, championship-order, previous-year, last-year-order, circuit, smart}
                            {--season= : Comma-separated years for last-year-order only (e.g. 2025,2026)}';

    protected $description = 'Run all algorithm-based bot seeders to populate predictions.';

    /** @var array<string, class-string<\Illuminate\Database\Seeder>> */
    private const BOT_SEEDERS = [
        'last' => BotPredictionsSeeder::class,
        'season' => ChampionshipOrderBotSeeder::class,
        'championship-order' => ChampionshipOrderBotSeeder::class,
        'random' => RandomBotSeeder::class,
        'previous-year' => PreviousYearChampionshipBotSeeder::class,
        'last-year-order' => LastYearChampionshipOrderBotSeeder::class,
        'circuit' => PreviousCircuitBotSeeder::class,
        'smart' => SmartWeightedBotSeeder::class,
    ];

    public function handle(): int
    {
        $only = $this->option('only');
        $names = $only
            ? array_map('trim', explode(',', $only))
            : array_keys(self::BOT_SEEDERS);

        $invalid = array_diff($names, array_keys(self::BOT_SEEDERS));
        if ($invalid !== []) {
            $this->error('Unknown bot(s): '.implode(', ', $invalid));
            $this->line('Available: '.implode(', ', array_keys(self::BOT_SEEDERS)));

            return Command::FAILURE;
        }

        foreach ($names as $name) {
            $seederClass = self::BOT_SEEDERS[$name];
            $this->info("Seeding {$name}...");

            if ($name === 'last-year-order' && $this->option('season')) {
                $seasonInput = $this->option('season');
                $seasons = array_values(array_map('intval', array_filter(explode(',', $seasonInput))));
                if ($seasons !== []) {
                    config(['f1.bot_seed_seasons' => $seasons]);
                }
            }

            $this->call('db:seed', ['--class' => $seederClass, '--no-interaction' => true]);

            if ($name === 'last-year-order' && $this->option('season')) {
                config(['f1.bot_seed_seasons' => null]);
            }
        }

        $this->info('Done.');

        return Command::SUCCESS;
    }
}
