<?php

namespace App\Modules\Logs\Support;

use CodeIgniter\HTTP\Request;

class RequestTracker
{
    private static array $storage = [];

    public static function initialize(Request $request): void
    {
        $key = self::getKey($request);
        
        if (isset(self::$storage[$key]['initialized'])) {
            return;
        }
        
        self::$storage[$key] = [
            'initialized' => true,
            'trace_id' => null,
            'cache_status' => 'MISS',
            'sql_count' => 0,
            'sql_time' => 0.0,
            'start_time' => null,
        ];
    }

    public static function setTraceId(Request $request, string $traceId): void
    {
        $key = self::getKey($request);
        self::$storage[$key]['trace_id'] = $traceId;
    }

    public static function getTraceId(Request $request): ?string
    {
        $key = self::getKey($request);
        return self::$storage[$key]['trace_id'] ?? null;
    }

    public static function setCacheStatus(Request $request, string $status): void
    {
        $key = self::getKey($request);
        
        if ((self::$storage[$key]['cache_status'] ?? '') === 'HIT') {
            return;
        }
        
        self::$storage[$key]['cache_status'] = strtoupper($status);
    }

    public static function getCacheStatus(Request $request): string
    {
        $key = self::getKey($request);
        return self::$storage[$key]['cache_status'] ?? 'MISS';
    }

    public static function incrementSqlCount(Request $request): void
    {
        $key = self::getKey($request);
        self::$storage[$key]['sql_count'] = (self::$storage[$key]['sql_count'] ?? 0) + 1;
    }

    public static function addSqlTime(Request $request, float $timeMs): void
    {
        $key = self::getKey($request);
        self::$storage[$key]['sql_time'] = (self::$storage[$key]['sql_time'] ?? 0) + $timeMs;
    }

    public static function getSqlStats(Request $request): array
    {
        $key = self::getKey($request);
        return [
            'count' => self::$storage[$key]['sql_count'] ?? 0,
            'time' => round(self::$storage[$key]['sql_time'] ?? 0, 2),
        ];
    }

    public static function setStartTime(Request $request): void
    {
        $key = self::getKey($request);
        if (!isset(self::$storage[$key]['start_time'])) {
            self::$storage[$key]['start_time'] = microtime(true);
        }
    }

    public static function getTotalTime(Request $request): float
    {
        $key = self::getKey($request);
        $startTime = self::$storage[$key]['start_time'] ?? null;
        return $startTime ? round((microtime(true) - $startTime) * 1000, 2) : 0;
    }

    public static function getAllStats(Request $request): array
    {
        return [
            'trace_id' => self::getTraceId($request),
            'cache_status' => self::getCacheStatus($request),
            'sql' => self::getSqlStats($request),
            'total_time' => self::getTotalTime($request),
        ];
    }

    public static function clear(Request $request): void
    {
        $key = self::getKey($request);
        unset(self::$storage[$key]);
    }

    public static function isInitialized(Request $request): bool
    {
        $key = self::getKey($request);
        return isset(self::$storage[$key]['initialized']);
    }

    private static function getKey(Request $request): string
    {
        return 'req_' . spl_object_id($request);
    }
}