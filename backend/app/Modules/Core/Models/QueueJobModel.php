<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Entities\QueueJobEntity;
use CodeIgniter\Model;

class QueueJobModel extends Model
{
    protected $table = 'queue_jobs';
    protected $primaryKey = 'id';
    protected $returnType = QueueJobEntity::class;
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'event_type',
        'payload',
        'status',
        'attempts',
        'max_attempts',
        'last_error',
        'processed_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public function findPending(int $limit = 10): array
    {
        return $this->where('status', self::STATUS_PENDING)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->find();
    }

    public function findProcessing(): array
    {
        return $this->where('status', self::STATUS_PROCESSING)
            ->where('processed_at <', date('Y-m-d H:i:s', strtotime('-5 minutes')))
            ->find();
    }

    public function createJob(string $eventType, array $payload, int $maxAttempts = 3): int
    {
        return $this->insert([
            'event_type' => $eventType,
            'payload' => json_encode($payload),
            'status' => self::STATUS_PENDING,
            'attempts' => 0,
            'max_attempts' => $maxAttempts,
        ]);
    }

    public function getStats(): array
    {
        return [
            'pending' => $this->where('status', self::STATUS_PENDING)->countAllResults(),
            'processing' => $this->where('status', self::STATUS_PROCESSING)->countAllResults(),
            'completed' => $this->where('status', self::STATUS_COMPLETED)->countAllResults(),
            'failed' => $this->where('status', self::STATUS_FAILED)->countAllResults(),
            'total' => $this->countAll(),
        ];
    }
}