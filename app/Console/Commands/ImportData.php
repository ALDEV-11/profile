<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use JsonException;

class ImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:import
                            {path : JSON file produced by data:export}
                            {--force : Replace existing data in the target database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import portfolio tables from a data:export JSON file';

    /**
     * Columns that MySQL stores as 0/1 and Postgres expects as booleans.
     *
     * @var array<string, array<int, string>>
     */
    protected const BOOLEANS = [
        'users' => ['two_factor_enabled'],
        'educations' => ['is_currently_studying'],
        'contact_messages' => ['is_read'],
    ];

    /**
     * All tables the importer understands, in foreign-key order.
     *
     * @var array<int, string>
     */
    protected const TABLES = [
        'users',
        'profiles',
        'educations',
        'hobbies',
        'galleries',
        'social_medias',
        'contact_messages',
        'visitors',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->argument('path');

        if (! File::exists($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        try {
            $payload = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error('Invalid JSON: '.$exception->getMessage());

            return self::FAILURE;
        }

        $tables = array_values(array_filter(
            self::TABLES,
            fn (string $table) => array_key_exists($table, $payload['tables'] ?? []),
        ));

        if ($tables === []) {
            $this->error('The file does not contain any known tables.');

            return self::FAILURE;
        }

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                $this->error("Missing table [{$table}]. Run `php artisan migrate --force` on the target database first.");

                return self::FAILURE;
            }
        }

        if (! $this->option('force') && Schema::hasTable('users') && DB::table('users')->exists()) {
            $this->error('The target database already contains users. Re-run with --force to replace the data.');

            return self::FAILURE;
        }

        $driver = DB::connection()->getDriverName();

        DB::transaction(function () use ($tables, $payload, $driver): void {
            $this->clearSessions();

            // Delete children before parents so foreign keys never block the wipe.
            foreach (array_reverse($tables) as $table) {
                DB::table($table)->delete();
            }

            foreach ($tables as $table) {
                $rows = array_map(
                    fn (array $row) => $this->prepareRow($table, $row),
                    $payload['tables'][$table],
                );

                foreach (array_chunk($rows, 100) as $chunk) {
                    DB::table($table)->insert($chunk);
                }

                $this->line(sprintf('  %-18s %d row(s)', $table, count($rows)));
            }

            if ($driver === 'pgsql') {
                $this->resetPostgresSequences($tables);
            }
        });

        $this->newLine();
        $this->info('Import complete.');

        return self::SUCCESS;
    }

    /**
     * Drop login state so imported sessions can never be reused.
     */
    protected function clearSessions(): void
    {
        foreach (['auth_sessions', 'sessions'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
    }

    /**
     * Normalise a row for the target database.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function prepareRow(string $table, array $row): array
    {
        foreach (self::BOOLEANS[$table] ?? [] as $column) {
            if (array_key_exists($column, $row)) {
                $row[$column] = filter_var($row[$column], FILTER_VALIDATE_BOOLEAN);
            }
        }

        if ($table === 'users') {
            // Short-lived login state must not travel between environments.
            foreach (['two_factor_secret', 'two_factor_otp', 'two_factor_otp_expires_at', 'remember_token'] as $column) {
                if (array_key_exists($column, $row)) {
                    $row[$column] = null;
                }
            }

            if (array_key_exists('two_factor_attempts', $row)) {
                $row['two_factor_attempts'] = 0;
            }
        }

        return $row;
    }

    /**
     * Point each Postgres sequence at the largest imported id.
     *
     * @param  array<int, string>  $tables
     */
    protected function resetPostgresSequences(array $tables): void
    {
        foreach ($tables as $table) {
            DB::statement(
                "SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE(MAX(id), 1), MAX(id) IS NOT NULL) FROM {$table}"
            );
        }
    }
}
