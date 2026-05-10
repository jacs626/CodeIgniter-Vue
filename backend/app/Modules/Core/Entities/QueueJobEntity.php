<?php

namespace App\Modules\Core\Entities;

use CodeIgniter\Entity\Entity;

class QueueJobEntity extends Entity
{
    protected $attributes = [
        'id' => null,
        'event_type' => null,
        'payload' => null,
        'status' => 'pending',
        'attempts' => 0,
        'max_attempts' => 3,
        'last_error' => null,
        'processed_at' => null,
        'created_at' => null,
        'updated_at' => null,
    ];

    protected $casts = [
        'id' => 'int',
        'attempts' => 'int',
        'max_attempts' => 'int',
    ];

    public function isPending(): bool
    {
        return $this->attributes['status'] === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->attributes['status'] === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->attributes['status'] === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->attributes['status'] === 'failed';
    }

    public function canRetry(): bool
    {
        return $this->attributes['attempts'] < $this->attributes['max_attempts']
            && $this->attributes['status'] === 'failed';
    }

    public function markProcessing(): self
    {
        $this->attributes['status'] = 'processing';
        return $this;
    }

    public function markCompleted(): self
    {
        $this->attributes['status'] = 'completed';
        $this->attributes['processed_at'] = date('Y-m-d H:i:s');
        return $this;
    }

    public function markFailed(string $error): self
    {
        $this->attributes['status'] = 'failed';
        $this->attributes['last_error'] = $error;
        $this->attributes['attempts']++;
        return $this;
    }

    public function incrementAttempts(): self
    {
        $this->attributes['attempts']++;
        return $this;
    }
}