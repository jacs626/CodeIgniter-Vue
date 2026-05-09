<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\QueueJobModel;
use App\Modules\Core\Entities\QueueJobEntity;
use CodeIgniter\Events\Events;

class QueueService
{
    protected QueueJobModel $model;
    protected array $listeners = [];

    public function __construct(?QueueJobModel $model = null)
    {
        $this->model = $model ?? model('App\Modules\Core\Models\QueueJobModel');
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        log_message($level, $message, $context);
    }

    public function enqueue(string $eventType, array $payload, int $maxAttempts = 3): int
    {
        $jobId = $this->model->createJob($eventType, $payload, $maxAttempts);

        $this->log('info', "[QUEUE] Enqueued job #{$jobId}: {$eventType}");

        return $jobId;
    }

    public function process(?int $limit = null): array
    {
        $processed = 0;
        $failed = 0;
        $limit = $limit ?? 10;

        $pendingJobs = $this->model->findPending($limit);

        $this->log('info', "[QUEUE] Processing {$limit} jobs (found: " . count($pendingJobs) . ")");

        foreach ($pendingJobs as $job) {
            $result = $this->processJob($job);

            if ($result) {
                $processed++;
            } else {
                $failed++;
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'total' => count($pendingJobs),
        ];
    }

    public function processJob(QueueJobEntity $job): bool
    {
        $jobId = $job->id;
        $eventType = $job->event_type;
        $payload = is_array($job->payload) ? $job->payload : json_decode($job->payload, true);

        $this->log('info', "[QUEUE] Processing job #{$jobId}: {$eventType}");

        try {
            $job->markProcessing();
            $this->model->save($job);

            $this->dispatchEvent($eventType, $payload);

            $job->markCompleted();
            $this->model->save($job);

            $this->log('info', "[QUEUE] Completed job #{$jobId}");

            return true;

        } catch (\Throwable $e) {
            $this->log('error', "[QUEUE] Failed job #{$jobId}: {$e->getMessage()}");

            $job->markFailed($e->getMessage());
            $this->model->save($job);

            if ($job->canRetry()) {
                $job->status = 'pending';
                $job->last_error = null;
                $this->model->save($job);
                $this->log('info', "[QUEUE] Job #{$jobId} re-queued for retry (attempt {$job->attempts}/{$job->max_attempts})");
            }

            return false;
        }
    }

    protected function dispatchEvent(string $eventType, array $payload): void
    {
        $className = $this->getEventClass($eventType);

        if (class_exists($className)) {
            $event = new $className($payload);
            Events::trigger($eventType, $event);
        } else {
            Events::trigger($eventType, $payload);
        }
    }

    protected function getEventClass(string $eventType): string
    {
        $map = [
            'producto.creado' => \App\Modules\Productos\Events\ProductoCreadoEvent::class,
            'producto.actualizado' => \App\Modules\Productos\Events\ProductoActualizadoEvent::class,
            'producto.eliminado' => \App\Modules\Productos\Events\ProductoEliminadoEvent::class,
        ];

        return $map[$eventType] ?? \App\Modules\Core\Events\BaseEvent::class;
    }

    public function getStats(): array
    {
        return $this->model->getStats();
    }

    public function retryFailed(int $maxAttempts = 3): int
    {
        $jobs = $this->model->where('status', 'failed')
            ->where('attempts <', $maxAttempts)
            ->find();

        $count = 0;
        foreach ($jobs as $job) {
            $job->status = 'pending';
            $job->last_error = null;
            $this->model->save($job);
            $count++;
        }

        $this->log('info', "[QUEUE] Re-queued {$count} failed jobs for retry");

        return $count;
    }
}