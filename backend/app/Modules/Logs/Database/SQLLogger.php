<?php

namespace App\Modules\Logs\Database;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Query;

class SQLLogger
{
    private static array $queries = [];
    private static array $queryTimes = [];
    private static float $requestStartTime = 0;
    private static ?string $currentTraceId = null;
    private static bool $enabled = true;
    private const SLOW_QUERY_THRESHOLD_MS = 50;

    public static function startRequest(string $traceId): void
    {
        self::$currentTraceId = $traceId;
        self::$requestStartTime = microtime(true);
        self::$queries[$traceId] = [];
        self::$queryTimes[$traceId] = 0;
    }

    public static function endRequest(string $traceId): array
    {
        $summary = [
            'trace_id' => $traceId,
            'query_count' => count(self::$queries[$traceId] ?? []),
            'total_sql_time_ms' => round(self::$queryTimes[$traceId] ?? 0, 2),
            'slow_queries' => [],
        ];

        if (isset(self::$queries[$traceId])) {
            foreach (self::$queries[$traceId] as $query) {
                if ($query['time_ms'] > self::SLOW_QUERY_THRESHOLD_MS) {
                    $summary['slow_queries'][] = [
                        'query' => $query['query'],
                        'time_ms' => $query['time_ms'],
                        'type' => $query['type'],
                    ];
                }
            }
        }

        unset(self::$queries[$traceId], self::$queryTimes[$traceId]);

        return $summary;
    }

    public static function logQuery(Query $query): void
    {
        if (!self::$enabled || !self::$currentTraceId) {
            return;
        }

        $sql = $query->getQuery();
        $timeMs = round($query->getDuration() * 1000, 2);
        $type = self::extractQueryType($sql);

        self::$queries[self::$currentTraceId][] = [
            'query' => $sql,
            'time_ms' => $timeMs,
            'type' => $type,
            'timestamp' => microtime(true),
        ];

        self::$queryTimes[self::$currentTraceId] += $query->getDuration() * 1000;

        $trace = self::$currentTraceId;
        $slowMarker = $timeMs > self::SLOW_QUERY_THRESHOLD_MS ? ' ⚠️ SLOW' : '';
        
        log_message('info', "[SQL] TRACE={$trace} | time={$timeMs}ms | {$type} | " . self::truncateQuery($sql) . $slowMarker);
    }

    public static function getQueries(string $traceId): array
    {
        return self::$queries[$traceId] ?? [];
    }

    public static function enable(): void
    {
        self::$enabled = true;
    }

    public static function disable(): void
    {
        self::$enabled = false;
    }

    private static function extractQueryType(string $sql): string
    {
        $sql = trim(strtoupper($sql));
        
        if (str_starts_with($sql, 'SELECT')) {
            return 'SELECT';
        }
        if (str_starts_with($sql, 'INSERT')) {
            return 'INSERT';
        }
        if (str_starts_with($sql, 'UPDATE')) {
            return 'UPDATE';
        }
        if (str_starts_with($sql, 'DELETE')) {
            return 'DELETE';
        }
        
        return strtok($sql, ' ');
    }

    private static function truncateQuery(string $sql, int $maxLength = 100): string
    {
        $sql = preg_replace('/\s+/', ' ', $sql);
        return strlen($sql) > $maxLength ? substr($sql, 0, $maxLength) . '...' : $sql;
    }

    public static function registerCallbacks(BaseConnection &$db): void
    {
        $db->setPostConnect(function ($conn) use ($db) {
            $db->resetSelectQueryPrefixes();
        });
    }
}