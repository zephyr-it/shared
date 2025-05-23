<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Commands\Scheduling\Central;

use Illuminate\Console\Command;
use ZephyrIt\Shared\Commands\Scheduling\Concerns\HandlesActivityLogCleanup;

class CleanupActivityLogCommand extends Command
{
    use HandlesActivityLogCleanup;

    protected $signature = 'toolkit:schedule:central:cleanup-activity-log
                            {--event= : Activity event type (created, updated, deleted, etc.)}
                            {--model= : The subject model class (e.g. App\Models\User)}
                            {--keep-days=0 : Retain logs from the last X days}
                            {--dry-run : Show what would be deleted without deleting}';

    protected $description = 'Clean up activity logs in the central application.';

    public function handle(): int
    {
        return $this->runCleanupLogic();
    }
}
