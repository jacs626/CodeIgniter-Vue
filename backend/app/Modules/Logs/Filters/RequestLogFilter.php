<?php

namespace App\Modules\Logs\Filters;

use App\Modules\Logs\Events\DatabaseEvents;
use App\Modules\Logs\Support\RequestTracker;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RequestLogFilter implements FilterInterface
{
    private const BLOCK_START = '┌────────────────────────────────────────────────────┐';
    private const BLOCK_END   = '└────────────────────────────────────────────────────┘';

    public function before(RequestInterface $request, $arguments = null)
    {
        RequestTracker::initialize($request);

        if (!RequestTracker::getTraceId($request)) {
            $traceId = $request->getHeaderLine('X-Trace-Id') ?: bin2hex(random_bytes(8));
            RequestTracker::setTraceId($request, $traceId);
        }

        RequestTracker::setStartTime($request);
        RequestTracker::setCacheStatus($request, 'MISS');

        $traceId = RequestTracker::getTraceId($request);
        
        DatabaseEvents::register($traceId);
        DatabaseEvents::setTraceId($traceId);

        log_message('info', self::BLOCK_START);
        log_message('info', "▶ TRACE={$traceId} | {$request->getMethod()} {$request->getUri()->getPath()}");
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $traceId = RequestTracker::getTraceId($request) ?? 'unknown';
        $totalTime = RequestTracker::getTotalTime($request);
        $cacheStatus = RequestTracker::getCacheStatus($request);
        $sqlStats = RequestTracker::getSqlStats($request);
        $status = $response->getStatusCode();

        log_message('info', "◀ {$status} | {$totalTime}ms");
        log_message('info', self::BLOCK_END);
        log_message('info', "📊 SUMMARY | TRACE={$traceId}");
        log_message('info', "   CACHE: {$cacheStatus}");
        log_message('info', "   SQL: {$sqlStats['count']} queries | {$sqlStats['time']}ms");
        log_message('info', "   TIME: {$totalTime}ms");
        log_message('info', self::BLOCK_END);

        RequestTracker::clear($request);
        DatabaseEvents::clear();
    }
}