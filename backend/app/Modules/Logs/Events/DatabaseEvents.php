<?php

namespace App\Modules\Logs\Events;

use App\Modules\Logs\Support\RequestTracker;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\Request;

class DatabaseEvents
{
    private static bool $registered = false;
    private static ?string $currentTraceId = null;

    public static function register(?string $traceId = null): void
    {
        if (self::$registered) {
            return;
        }

        self::$currentTraceId = $traceId;

        Events::on('DBQuery', function ($query) {
            $timeMs = round($query->getDuration() * 1000, 2);
            $sql = substr($query->getQuery(), 0, 80);
            $type = strtoupper(strtok($query->getQuery(), ' '));
            
            $request = service('request');
            if ($request instanceof Request) {
                RequestTracker::incrementSqlCount($request);
                RequestTracker::addSqlTime($request, $timeMs);
            }
            
            log_message('info', "💾 SQL | {$timeMs}ms | {$type} | {$sql}");
        });

        self::$registered = true;
    }

    public static function setTraceId(string $traceId): void
    {
        self::$currentTraceId = $traceId;
    }

    public static function getTraceId(): ?string
    {
        return self::$currentTraceId;
    }

    public static function clear(): void
    {
        self::$currentTraceId = null;
        self::$registered = false;
    }
}