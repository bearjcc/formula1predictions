<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeUsers extends Command
{
    protected $signature = 'app:merge-users
                            {target_email? : Email of the user to keep (default: zoepasternack@gmail.com)}
                            {source_email? : Email of the user to merge and remove (default: sunny@example.com)}
                            {--execute : Run the merge; default is dry-run}';

    protected $description = 'Merge source user into target user (reassign all data, then delete source). Dry-run by default.';

    public function handle(): int
    {
        $targetEmail = $this->argument('target_email') ?? 'zoepasternack@gmail.com';
        $sourceEmail = $this->argument('source_email') ?? 'sunny@example.com';
        $execute = $this->option('execute');

        $target = User::where('email', $targetEmail)->first();
        $source = User::where('email', $sourceEmail)->first();

        if (! $target) {
            $this->error("Target user not found: {$targetEmail}");

            return Command::FAILURE;
        }
        if (! $source) {
            $this->error("Source user not found: {$sourceEmail}");

            return Command::FAILURE;
        }
        if ($target->id === $source->id) {
            $this->error('Target and source are the same user.');

            return Command::FAILURE;
        }

        $this->info("Target (keep): {$targetEmail} (id={$target->id})");
        $this->info("Source (merge then remove): {$sourceEmail} (id={$source->id})");

        $targetId = $target->id;
        $sourceId = $source->id;

        $predictionsSource = DB::table('predictions')->where('user_id', $sourceId)->get();
        $predictionsTargetKeys = DB::table('predictions')
            ->where('user_id', $targetId)
            ->get()
            ->keyBy(fn ($p) => "{$p->type}|{$p->season}|" . (string) $p->race_round);

        $conflicts = [];
        $toReassign = [];
        foreach ($predictionsSource as $p) {
            $key = "{$p->type}|{$p->season}|" . (string) $p->race_round;
            if ($predictionsTargetKeys->has($key)) {
                $conflicts[] = ['type' => $p->type, 'season' => $p->season, 'race_round' => $p->race_round, 'id' => $p->id];
            } else {
                $toReassign[] = $p->id;
            }
        }

        $feedbackCount = DB::table('feedback')->where('user_id', $sourceId)->count();
        $newsCount = DB::table('news')->where('user_id', $sourceId)->count();
        $sessionsCount = DB::table('sessions')->where('user_id', $sourceId)->count();
        $userMorphClass = (new User)->getMorphClass();
        $notificationsCount = DB::table('notifications')
            ->where('notifiable_type', $userMorphClass)
            ->where('notifiable_id', $sourceId)
            ->count();

        $passwordResetCount = DB::table('password_reset_tokens')->where('email', $sourceEmail)->count();

        $this->newLine();
        $this->info('Would change:');
        $this->table(
            ['Table', 'Action', 'Count'],
            [
                ['predictions', 'reassign to target', count($toReassign)],
                ['predictions', 'delete (conflict with target)', count($conflicts)],
                ['feedback', 'reassign to target', $feedbackCount],
                ['news', 'reassign to target', $newsCount],
                ['sessions', 'reassign to target', $sessionsCount],
                ['notifications', 'reassign to target', $notificationsCount],
                ['password_reset_tokens', 'delete source email', $passwordResetCount],
                ['users', 'delete source user', 1],
            ]
        );

        if (count($conflicts) > 0) {
            $this->warn('Prediction conflicts (source prediction would be deleted, target kept):');
            $this->table(['type', 'season', 'race_round', 'source prediction id'], array_map(fn ($c) => [$c['type'], $c['season'], $c['race_round'], $c['id']], $conflicts));
        }

        if (! $execute) {
            $this->newLine();
            $this->warn('Dry-run. Use --execute to perform the merge.');

            return Command::SUCCESS;
        }

        $this->newLine();
        if (! $this->confirm('Proceed with merge?', true)) {
            $this->info('Aborted.');

            return Command::SUCCESS;
        }

        DB::transaction(function () use ($targetId, $sourceId, $sourceEmail, $toReassign, $conflicts, $userMorphClass) {
            foreach ($toReassign as $predictionId) {
                DB::table('predictions')->where('id', $predictionId)->update(['user_id' => $targetId]);
            }
            foreach ($conflicts as $c) {
                DB::table('predictions')->where('id', $c['id'])->delete();
            }

            DB::table('feedback')->where('user_id', $sourceId)->update(['user_id' => $targetId]);
            DB::table('news')->where('user_id', $sourceId)->update(['user_id' => $targetId]);
            DB::table('sessions')->where('user_id', $sourceId)->update(['user_id' => $targetId]);
            DB::table('notifications')
                ->where('notifiable_type', $userMorphClass)
                ->where('notifiable_id', $sourceId)
                ->update(['notifiable_id' => $targetId]);

            DB::table('password_reset_tokens')->where('email', $sourceEmail)->delete();
            DB::table('users')->where('id', $sourceId)->delete();
        });

        $this->info('Merge completed. Source user removed.');

        return Command::SUCCESS;
    }
}
