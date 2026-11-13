<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:export
                            {path? : Destination JSON file}
                            {--with-visitors : Include the anonymous visitor analytics table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export portfolio tables to JSON for migrating between databases';

    /**
     * Application tables in foreign-key order.
     *
     * @var array<int, string>
     */
    public const TABLES = [
        'users',
        'profiles',
        'educations',
        'hobbies',
        'galleries',
        'social_medias',
        'contact_messages',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tables = self::TABLES;

        if ($this->option('with-visitors')) {
            $tables[] = 'visitors';
        }

        $path = $this->argument('path')
            ?: storage_path('app/private/exports/portfolio-'.now()->format('Ymd-His').'.json');

        File::ensureDirectoryExists(dirname($path));

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'source_connection' => DB::connection()->getName(),
            'source_database' => DB::connection()->getDatabaseName(),
            'tables' => [],
        ];

        foreach ($tables as $table) {
            $rows = DB::table($table)->orderBy('id')->get()
                ->map(fn ($row) => (array) $row)
                ->all();

            $payload['tables'][$table] = $rows;

            $this->line(sprintf('  %-18s %d row(s)', $table, count($rows)));
        }

        File::put(
            $path,
            json_encode(
                $payload,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            ).PHP_EOL,
        );

        $this->newLine();
        $this->info("Exported to {$path}");

        return self::SUCCESS;
    }
}
