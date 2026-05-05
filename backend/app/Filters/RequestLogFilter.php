<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RequestLogFilter implements FilterInterface
{
    private array $requestTimes = [];

    public function before(RequestInterface $request, $arguments = null)
    {
        $traceId = $request->getHeader('X-Trace-Id')?->getValue() ?: bin2hex(random_bytes(8));
        
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();
        
        $this->requestTimes[$traceId] = microtime(true);
        
        log_message('info', "TRACE={$traceId}");
        log_message('info', "[REQUEST] {$method} {$uri}");
        log_message('info', str_repeat('-', 50));
        
        $request->setHeader('X-Trace-Id', $traceId);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $traceId = $request->getHeader('X-Trace-Id')?->getValue() ?: 'unknown';
        $uri = $request->getUri()->getPath();
        $status = $response->getStatusCode();
        
        $startTime = $this->requestTimes[$traceId] ?? microtime(true);
        $totalTime = round((microtime(true) - $startTime) * 1000, 2);
        
        log_message('info', "[RESPONSE] {$status} | total={$totalTime}ms");
        log_message('info', str_repeat('=', 50));
    }
}