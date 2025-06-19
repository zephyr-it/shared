<?php

namespace ZephyrIt\Shared\Support;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * BaseSeeder.
 *
 * A recursive seeder that ensures:
 * - Only internal/leaf seeders are logged (never parent/group seeders)
 * - Nested seeders are run recursively via getSeeders()
 * - Logging is done only for seeders that actually insert data
 */
abstract class BaseSeeder extends Seeder
{
    /**
     * Entrypoint for Laravel's seeding system.
     */
    public function run(): void
    {
        activity()->disableLogging();

        try {
            $this->runSeederRecursively(static::class);
        } finally {
            activity()->enableLogging();
        }
    }

    /**
     * Recursively runs nested seeders and logs leaf-level ones only.
     */
    protected function runSeederRecursively(string $seeder): void
    {
        if ($this->hasSeeded($seeder)) {
            return;
        }

        $instance = app($seeder);

        $isGroupSeeder = method_exists($instance, 'getSeeders') && ! empty($instance->getSeeders());

        if ($isGroupSeeder) {
            foreach ($instance->getSeeders() as $nested) {
                $this->runSeederRecursively($nested);
            }

            return; // 🚫 Do not log group seeders
        }

        $this->call($seeder);
        $this->logSeeder($seeder);
    }

    /**
     * Check if a seeder has already run.
     */
    protected function hasSeeded(string $seeder): bool
    {
        return Schema::hasTable('seeder_logs') &&
            DB::table('seeder_logs')->where('seeder', $seeder)->exists();
    }

    /**
     * Log a seeder as completed.
     */
    protected function logSeeder(string $seeder): void
    {
        DB::table('seeder_logs')->insertOrIgnore([
            'seeder' => $seeder,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Must be implemented to define child seeders.
     * Return an array of fully-qualified seeder class names.
     *
     * @return array<class-string<Seeder>>
     */
    abstract public function getSeeders(): array;

    /**
     * Optional for leaf-seeders with direct logic instead of getSeeders().
     */
    public function handle(): void
    {
        // Only used in non-nested contexts
    }
}
