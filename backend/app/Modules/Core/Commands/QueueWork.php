<?php

namespace App\Modules\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Modules\Core\Services\QueueService;

class QueueWork extends BaseCommand
{
    protected $group = 'Queue';
    protected $name = 'queue:work';
    protected $description = 'Process queued jobs';
    protected $usage = 'php spark queue:work [--limit=10] [--once]';

    protected $options = [
        '--limit' => 'Number of jobs to process (default: 10)',
        '--once' => 'Process only once and exit (default: loop)',
    ];

    protected QueueService $queueService;

    public function __construct()
    {
        parent::__construct();
        $this->queueService = service('queue');
    }

    public function run(array $params)
    {
        $limit = $params['limit'] ?? 10;
        $once = isset($params['once']);
        $sleepSeconds = 2;

        CLI::write('🎬 Queue Worker Started', 'green');
        CLI::write('Press Ctrl+C to stop', 'yellow');

        $stats = $this->queueService->getStats();
        CLI::write("📊 Queue Stats: pending={$stats['pending']} processing={$stats['processing']} failed={$stats['failed']}", 'cyan');

        $iteration = 0;
        $totalProcessed = 0;
        $totalFailed = 0;

        while (true) {
            $iteration++;

            CLI::write("\n--- Iteration #{$iteration} ---", 'white');

            $result = $this->queueService->process((int) $limit);

            $totalProcessed += $result['processed'];
            $totalFailed += $result['failed'];

            CLI::write("  Processed: {$result['processed']}", 'green');
            CLI::write("  Failed: {$result['failed']}", $result['failed'] > 0 ? 'red' : 'green');

            $stats = $this->queueService->getStats();
            CLI::write("  📊 Remaining: pending={$stats['pending']} failed={$stats['failed']}", 'cyan');

            if ($once) {
                break;
            }

            if ($result['processed'] === 0 && $result['failed'] === 0) {
                CLI::write("  💤 Sleeping {$sleepSeconds}s (no jobs)...", 'yellow');
                sleep($sleepSeconds);
            }
        }

        CLI::write("\n✅ Worker stopped", 'green');
        CLI::write("Total processed: {$totalProcessed}, Total failed: {$totalFailed}", 'cyan');
    }
}