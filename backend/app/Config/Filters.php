<?php

namespace Config;

use App\Filters\CorsFilter;
use App\Modules\Auth\Filters\AuthFilter;
use App\Modules\Logs\Filters\RequestLogFilter;
use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'         => CorsFilter::class,
        'forcehttps'   => ForceHTTPS::class,
        'pagecache'    => PageCache::class,
        'performance'=> PerformanceMetrics::class,
        'auth'        => AuthFilter::class,
        'requestlog'   => RequestLogFilter::class,
    ];

    public array $required = [
        'before' => [],
        'after' => [],
    ];

    public array $globals = [
        'before' => ['cors'],
        'after' => [],
    ];

    public array $methods = [];

    public array $filters = [
        'requestlog' => [
            'before' => ['productos', 'productos/(:num)'],
            'after' => ['productos', 'productos/(:num)'],
        ],
    ];
}
