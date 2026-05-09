<?php

namespace App\Modules\Core\Listeners;

use App\Modules\Core\Events\BaseEvent;
use CodeIgniter\Cache\CacheInterface;

class InvalidarCacheProductoListener
{
    protected CacheInterface $cache;

    public function __construct(?CacheInterface $cache = null)
    {
        $this->cache = $cache ?? service('cache');
    }

    public function handle(BaseEvent $event): void
    {
        $eventName = $event->getName();
        $data = $event->getData();

        $cachePrefix = 'productos_';

        $versionKey = $cachePrefix . 'version';
        $currentVersion = $this->cache->get($versionKey);
        $newVersion = is_numeric($currentVersion) ? (int) $currentVersion + 1 : 1;

        $this->cache->save($versionKey, $newVersion);

        log_message('info', "[LISTENER:InvalidarCache] Cache invalidada para evento {$eventName}, versión: {$newVersion}");
    }
}