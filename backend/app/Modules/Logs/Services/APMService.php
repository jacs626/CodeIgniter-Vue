<?php

namespace App\Modules\Logs\Services;

use App\Modules\Logs\Database\SQLLogger;
use CodeIgniter\Config\BaseService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Events;

class APMService extends BaseService
{
    private static bool $initialized = false;

    public static function init(?string $traceId = null): void
    {
        if (self::$initialized) {
            return;
        }

        $db = database();

        $db->setPreQueryCallback(function ($query) {
            $query->setStartTime(microtime(true));
        });

        $db->setPostQueryCallback(function ($query) {
            SQLLogger::logQuery($query);
        });

        if ($traceId) {
            SQLLogger::startRequest($traceId);
        }

        self::$initialized = true;
    }

    public static function startRequest(string $traceId): void
    {
        SQLLogger::startRequest($traceId);
    }

    public static function endRequest(string $traceId): array
    {
        return SQLLogger::endRequest($traceId);
    }

    public static function getQueries(string $traceId): array
    {
        return SQLLogger::getQueries($traceId);
    }
}