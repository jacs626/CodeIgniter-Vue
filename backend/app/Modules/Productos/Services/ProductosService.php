<?php

namespace App\Modules\Productos\Services;

use App\Modules\Productos\Models\ProductoModel;
use App\Modules\Logs\Events\DatabaseEvents;
use App\Modules\Logs\Support\RequestTracker;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Request;

class ProductosService
{
    protected ProductoModel $model;
    protected CacheInterface $cache;
    protected BaseConnection $db;
    protected const CACHE_TTL = 60;
    protected const CACHE_PREFIX = 'productos_';

    private ?string $traceId = null;
    private ?Request $request = null;
    private float $startTime = 0;

    public function __construct(
        ?Request $request = null,
        ?ProductoModel $model = null,
        ?CacheInterface $cache = null,
        ?BaseConnection $db = null
    ) {
        $this->model = $model ?? model('App\Modules\Productos\Models\ProductoModel');
        $this->cache = $cache ?? service('cache');
        $this->db = $db ?? \Config\Database::connect();
        $this->db->transException(true);
        $this->traceId = DatabaseEvents::getTraceId();
        $this->request = $request ?? service('request');
    }

    public function obtenerTodos(?string $q = null, bool $soloOfertas = false, int $perPage = 10, int $page = 1): array
    {
        $this->startTime = microtime(true);
        $cacheKey = $this->generateCacheKey($q, $soloOfertas, $perPage, $page);
        $cacheKeyShort = substr($cacheKey, -16);
        
        $cached = $this->cache->get($cacheKey);
        $duration = round((microtime(true) - $this->startTime) * 1000, 2);
        
        if (!is_array($cached) || !isset($cached['data'])) {
            $cached = null;
        }
        
        if ($cached !== null) {
            log_message('info', "🚀 CACHE | HIT | {$duration}ms");
            
            if ($this->request instanceof Request) {
                RequestTracker::setCacheStatus($this->request, 'HIT');
            }
            
            $pager = service('pager');
            $pager->store('default', $page, $cached['pagerData']['perPage'], $cached['pagerData']['total'], 0);
            
            return [
                'data' => $cached['data'],
                'pager' => $pager,
                'cache_hit' => true
            ];
        }

        log_message('info', "🚀 CACHE | MISS | {$duration}ms");
        
        if ($this->request instanceof Request) {
            RequestTracker::setCacheStatus($this->request, 'MISS');
        }
        
        $result = $this->model->paginateWithSearch($q, $soloOfertas, $perPage, $page);
        
        $this->cache->save($cacheKey, [
            'data' => $result['data'],
            'pagerData' => [
                'total' => $result['pager']->getTotal('default'),
                'perPage' => $result['pager']->getPerPage('default'),
                'pageCount' => $result['pager']->getPageCount('default')
            ]
        ], self::CACHE_TTL);
        
        log_message('info', "💾 CACHE | SAVE | ttl=" . self::CACHE_TTL . 's');

        return $result;
    }

    protected function generateCacheKey(?string $q, bool $soloOfertas, int $perPage, int $page): string
    {
        $q = $q ? trim(strtolower($q)) : null;
        $version = $this->getCacheVersion();
        return self::CACHE_PREFIX . 'v' . $version . '_' . md5("q={$q}&ofertas={$soloOfertas}&perPage={$perPage}&page={$page}");
    }

    protected function getCacheVersion(): int
    {
        $version = $this->cache->get(self::CACHE_PREFIX . 'version');
        return is_numeric($version) ? (int) $version : 1;
    }

    protected function invalidateCache(): void
    {
        $version = $this->getCacheVersion();
        $newVersion = $version + 1;
        $this->cache->save(self::CACHE_PREFIX . 'version', $newVersion);
        
        log_message('info', "🚀 CACHE | INVALIDATED | v{$version}→v{$newVersion}");
        
        if ($this->request instanceof Request) {
            RequestTracker::setCacheStatus($this->request, 'MISS');
        }
    }

    public function obtenerPorId(int $id)
    {
        return $this->model->find($id);
    }

    public function crear(array $data)
    {
        if (empty($data)) {
            return false;
        }
        
        return $this->executeTransaction(function () use ($data) {
            $result = $this->model->insert($data);
            
            if ($result === false) {
                throw new \RuntimeException('Insert failed');
            }
            
            return $result;
        }, 'CREATE');
    }

    public function actualizar(int $id, array $data)
    {
        if ($id <= 0 || empty($data)) {
            return false;
        }
        
        return $this->executeTransaction(function () use ($id, $data) {
            $existing = $this->model->find($id);
            
            if (!$existing) {
                throw new \RuntimeException("Producto not found: {$id}");
            }
            
            if (!$this->model->update($id, $data)) {
                throw new \RuntimeException("Update failed for id: {$id}");
            }
            
            return $this->model->find($id);
        }, 'UPDATE', $id);
    }

    public function eliminar(int $id)
    {
        return $this->executeTransaction(function () use ($id) {
            $existing = $this->model->find($id);
            
            if (!$existing) {
                throw new \RuntimeException("Producto not found: {$id}");
            }
            
            if (!$this->model->delete($id)) {
                throw new \RuntimeException("Delete failed for id: {$id}");
            }
            
            return true;
        }, 'DELETE', $id);
    }

    public function obtenerAlertas(?string $since = null): array
    {
        $sinceLog = $since ?? 'primera vez';
        log_message('info', "[ALERTAS] Obteniendo productos | since={$sinceLog}");
        
        $productos = $this->model->findByPrecioEnOfertaSince($since);
        
        $count = count($productos);
        log_message('info', "[ALERTAS] Se encontraron {$count} productos en alerta");
        
        if ($count === 0) {
            log_message('info', '[ALERTAS] Sin nuevas alertas');
        }
        
        return $productos;
    }

    private function executeTransaction(callable $callback, string $action, ?int $id = null): mixed
    {
        if ($id !== null && $id <= 0) {
            return false;
        }
        
        $trace = $this->traceId ?? 'no-trace';
        $context = $id !== null ? " | id={$id}" : '';
        
        try {
            log_message('info', "[TX] START | {$action} | TRACE={$trace}{$context}");
            
            $this->db->transBegin();
            
            $result = $callback();
            
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed internally');
            }
            
            $this->db->transCommit();
            
            log_message('info', "[TX] COMMIT | {$action} | TRACE={$trace}");
            
            $this->invalidateCache();
            
            return $result;
            
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', "[TX] ROLLBACK | {$action} | TRACE={$trace} | {$e->getMessage()}");
            return false;
        }
    }
}