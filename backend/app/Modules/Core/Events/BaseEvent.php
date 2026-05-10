<?php

namespace App\Modules\Core\Events;

use CodeIgniter\Events\Events;
use CodeIgniter\Log\Logger;

abstract class BaseEvent
{
    protected string $name;
    protected array $data;
    protected Logger $logger;

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->logger = service('logger');
    }

    abstract public function getName(): string;

    public function getData(): array
    {
        return $this->data;
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function dispatch(): void
    {
        $eventName = $this->getName();

        $this->logger->info("[EVENT] Dispatching: {$eventName}", $this->data);

        Events::trigger($eventName, $this);

        $this->logger->info("[EVENT] Dispatched: {$eventName}");
    }

    public function dispatchAsync(): void
    {
        $queueService = service('queue');
        $queueService->enqueue($this->getName(), $this->getData());
    }

    public function toArray(): array
    {
        return [
            'event' => $this->getName(),
            'data' => $this->getData(),
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }
}