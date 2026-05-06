<?php

namespace App\Modules\Logs\Config;

use App\Modules\Logs\Events\DatabaseEvents;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function apmLogger($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('apmLogger');
        }

        return new class {
            public function init(string $traceId): void
            {
                DatabaseEvents::register($traceId);
            }

            public function setTraceId(string $traceId): void
            {
                DatabaseEvents::setTraceId($traceId);
            }

            public function getTraceId(): ?string
            {
                return DatabaseEvents::getTraceId();
            }
        };
    }
}