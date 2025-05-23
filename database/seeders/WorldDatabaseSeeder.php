<?php

namespace ZephyrIt\Shared\Database\seeders;

use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WorldDatabaseSeeder extends Seeder
{
    protected $backupUrls = [
        'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/refs/heads/master/sql/world.sql',
    ];

    /**
     * Run the database seeds.
     */
    public function run()
    {
        foreach ($this->backupUrls as $url) {
            if ($this->processBackupUrl($url)) {
                return;
            }
        }

        $this->command->error('All attempts to fetch the SQL file failed.');
    }

    /**
     * Process a single backup URL.
     */
    protected function processBackupUrl(string $url): bool
    {
        $this->command->info("Fetching SQL file from URL: {$url}");

        try {
            $response = Http::timeout(120)
                ->retry(3, 5000)
                ->get($url);

            if ($response->ok()) {
                $this->command->info('Successfully fetched SQL file.');
                $this->executeSqlFromStream($response->body());

                return true;
            }

            $this->command->error("Failed to fetch SQL file. HTTP Status: {$response->status()}");
        } catch (Exception $e) {
            $this->command->error("Exception occurred while fetching URL: {$url}. Exception: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Execute SQL queries from the streamed response.
     */
    protected function executeSqlFromStream(string $sqlContent)
    {
        $tempFile = tmpfile();
        fwrite($tempFile, $sqlContent);
        rewind($tempFile);

        $this->command->info('Processing SQL file...');
        $queryBuffer = '';

        while (($line = fgets($tempFile)) !== false) {
            $queryBuffer .= $line;

            if (str_ends_with(trim($line), ';')) {
                $this->executeSqlQuery(trim($queryBuffer));
                $queryBuffer = '';
            }
        }

        fclose($tempFile);
        $this->command->info('SQL file processing completed.');
    }

    /**
     * Execute a single SQL query.
     */
    protected function executeSqlQuery(string $query)
    {
        try {
            DB::unprepared($query);
        } catch (Exception $e) {
            $this->command->error("Error executing query: {$query}. Exception: " . $e->getMessage());
        }
    }
}
