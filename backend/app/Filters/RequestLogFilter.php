<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RequestLogFilter implements FilterInterface
{
    private static array $requestTimes = [];
    private static array $traceIds = [];

    public function before(RequestInterface $request, $arguments = null)
    {
        $traceId = $request->getHeaderLine('X-Trace-Id') 
            ?: bin2hex(random_bytes(8));

        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();

        self::$traceIds[spl_object_id($request)] = $traceId;
        self::$requestTimes[$traceId] = microtime(true);

        log_message('info', "TRACE={$traceId}");
        log_message('info', "[REQUEST] {$method} {$uri}");
        log_message('info', str_repeat('-', 50));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $key = spl_object_id($request);
        $traceId = self::$traceIds[$key] ?? 'unknown';

        $uri = $request->getUri()->getPath();
        $status = $response->getStatusCode();

        $startTime = self::$requestTimes[$traceId] ?? microtime(true);
        $totalTime = round((microtime(true) - $startTime) * 1000, 2);

        log_message('info', "[RESPONSE] {$uri} | {$status} | {$totalTime}ms");
        log_message('info', str_repeat('=', 50));
    }
}