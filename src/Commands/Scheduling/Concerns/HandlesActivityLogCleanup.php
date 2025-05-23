<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Commands\Scheduling\Concerns;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

trait HandlesActivityLogCleanup
{
    public function runCleanupLogic(): int
    {
        $event = $this->option('event');
        $model = $this->option('model');
        $keepDays = (int) $this->option('keep-days');
        $dryRun = $this->option('dry-run');

        if (! $event) {
            $this->error('The --event option is required. Use --event=all to delete all activity logs.');

            return Command::FAILURE;
        }

        $query = Activity::query();

        if ($event !== 'all') {
            $query->where('event', $event);
        }

        if ($model) {
            $query->where('subject_type', $model);
        }

        if ($keepDays > 0) {
            $query->where('created_at', '<', now()->subDays($keepDays));
        }

        $count = $query->count();

        if ($dryRun) {
            $this->line("Dry Run: {$count} activity logs would be deleted.");

            return Command::SUCCESS;
        }

        $deleted = $query->delete();

        $this->info("Deleted {$deleted} activity logs"
            . ($event !== 'all' ? " with event '{$event}'" : '')
            . ($model ? " for model '{$model}'" : '')
            . ($keepDays ? " older than {$keepDays} days" : '') . '.');

        return Command::SUCCESS;
    }
}
