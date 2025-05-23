<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Commands\Scheduling\Tenant;

use Illuminate\Console\Command;
use ZephyrIt\Shared\Commands\Scheduling\Concerns\HandlesActivityLogCleanup;
use ZephyrIt\Shared\Support\SafeHasTenantsOption;
use ZephyrIt\Shared\Support\SafeTenantAwareCommand;

class CleanupActivityLogCommand extends Command
{
    use HandlesActivityLogCleanup;
    use SafeHasTenantsOption;
    use SafeTenantAwareCommand;

    protected $signature = 'toolkit:schedule:tenant:cleanup-activity-log
                            {--event= : Activity event type (created, updated, deleted, etc.)}
                            {--model= : The subject model class (e.g. App\Models\User)}
                            {--keep-days=0 : Retain logs from the last X days}
                            {--dry-run : Show what would be deleted without deleting}';

    protected $description = 'Clean up activity logs in tenant databases.';

    public function handle(): int
    {
        return $this->runCleanupLogic();
    }

    public function tags(): array
    {
        return ['tenant:' . tenant('id')];
    }
}
