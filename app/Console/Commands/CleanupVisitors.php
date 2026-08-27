<?php

namespace App\Console\Commands;

use App\Repositories\VisitorRepository;
use Illuminate\Console\Command;

class CleanupVisitors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visitors:cleanup {--days=90 : Delete visitor records older than this many days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old anonymous visitor records to keep the table lean';

    /**
     * Execute the console command.
     */
    public function handle(VisitorRepository $repository): int
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error('The --days option must be at least 1.');

            return self::FAILURE;
        }

        $deleted = $repository->prune($days);

        $this->info("Deleted {$deleted} visitor record(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
