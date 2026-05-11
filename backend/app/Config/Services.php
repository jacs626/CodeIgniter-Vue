<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function productoService($getShared = true, $request = null)
    {
        if ($getShared) {
            return static::getSharedInstance('productoService');
        }

        return new \App\Modules\Productos\Services\ProductosService($request);
    }

    public static function queue($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('queue');
        }

        return new \App\Modules\Core\Services\QueueService();
    }

    public static function queueJobModel($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('queueJobModel');
        }

        return new \App\Modules\Core\Models\QueueJobModel();
    }

    public static function authService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('authService');
        }

        return new \App\Modules\Auth\Services\AuthService();
    }
}