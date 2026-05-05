<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RequestLogFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();
        $timestamp = date('Y-m-d H:i:s');

        log_message('info', "REQUEST | {$method} | {$uri} | {$timestamp}");
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $uri = $request->getUri()->getPath();
        $status = $response->getStatusCode();
        $timestamp = date('Y-m-d H:i:s');

        log_message('info', "RESPONSE | {$uri} | {$status} | {$timestamp}");
    }
}