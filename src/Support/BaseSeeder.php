<?php

namespace ZephyrIt\Shared\Support;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class BaseSeeder extends Seeder
{
    /**
     * Main entrypoint for the seeder.
     */
    public function run(): void
    {
        activity()->disableLogging();

        try {
            if ($this->hasSeeded(static::class)) {
                return;
            }

            if ($seeders = $this->getSeeders()) {
                foreach ($seeders as $nestedSeeder) {
                    $this->call($nestedSeeder);
                }
            } else {
                $this->handle();
            }

            $this->logSeeder(static::class);
        } finally {
            activity()->enableLogging();
        }
    }

    /**
     * Override to define seeder logic.
     */
    public function handle(): void
    {
        // Your seeding logic here.
    }

    /**
     * Override to return nested seeders.
     */
    public function getSeeders(): array
    {
        return [];
    }

    /**
     * Check if this seeder has already run.
     */
    protected function hasSeeded(string $seeder): bool
    {
        return Schema::hasTable('seeder_log') &&
               DB::table('seeder_log')->where('seeder', $seeder)->exists();
    }

    /**
     * Log that this seeder has run.
     */
    protected function logSeeder(string $seeder): void
    {
        DB::table('seeder_log')->insertOrIgnore([
            'seeder' => $seeder,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
