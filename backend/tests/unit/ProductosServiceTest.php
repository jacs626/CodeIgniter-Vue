<?php

namespace Tests\Unit;

use App\Modules\Productos\Services\ProductosService;
use App\Modules\Productos\Models\ProductoModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Database\BaseConnection;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests para ProductosService
 * 
 * Arquitectura:
 * - Service controla transacciones (no Model)
 * - Cache versioning para invalidación
 * - Errores de negocio → throw → catch → rollback
 */
final class ProductosServiceTest extends CIUnitTestCase
{
    private MockObject $mockModel;
    private MockObject $mockCache;
    private MockObject $mockDb;
    private ?ProductosService $service = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockModel = $this->createMock(ProductoModel::class);
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->mockDb = $this->createMock(BaseConnection::class);
    }

    private function createService(): ProductosService
    {
        return new ProductosService(null, $this->mockModel, $this->mockCache, $this->mockDb);
    }

    // ============================================================
    // HELPERS - Reducir repetición
    // ============================================================

    private function expectTransactionSuccess(): void
    {
        $this->mockDb->expects($this->once())->method('transBegin')->willReturn(true);
        $this->mockDb->expects($this->once())->method('transStatus')->willReturn(true);
        $this->mockDb->expects($this->once())->method('transCommit')->willReturn(true);
        $this->mockDb->expects($this->never())->method('transRollback');
    }

    private function expectTransactionRollback(): void
    {
        $this->mockDb->expects($this->once())->method('transBegin')->willReturn(true);
        $this->mockDb->expects($this->exactly(0))->method('transStatus');
        $this->mockDb->expects($this->once())->method('transRollback')->willReturn(true);
        $this->mockDb->expects($this->never())->method('transCommit');
    }

    private function expectNoCacheSave(): void
    {
        $this->mockCache->expects($this->never())->method('save');
    }

    private function expectCacheSave(): void
    {
        // Al menos una vez - para métodos críticos
        $this->mockCache->expects($this->atLeastOnce())->method('save');
    }

    private function expectCacheSaveExact(int $times): void
    {
        $this->mockCache->expects($this->exactly($times))->method('save');
    }

    private function expectCacheSaveAtLeast(int $times): void
    {
        $this->mockCache->expects($this->atLeast($times))->method('save');
    }

    private function expectCacheSaveWithContent(): void
    {
        $this->mockCache->expects($this->atLeastOnce())
            ->method('save')
            ->with(
                $this->stringContains('productos'),
                $this->callback(fn($data) => is_array($data) && isset($data['data']))
            );
    }

    private function stubCacheVersion(int $version = 1): void
    {
        // Preciso: solo intercepta la key de version
        $this->mockCache
            ->method('get')
            ->willReturnCallback(function ($key) use ($version) {
                if (str_contains($key, 'version')) {
                    return $version;
                }
                return null;
            });
    }

    private function createMockPager(): object
    {
        return new class {
            public function getTotal(string $group = 'default'): int {
                return 1;
            }
            public function getPerPage(string $group = 'default'): int {
                return 10;
            }
            public function getPageCount(string $group = 'default'): int {
                return 1;
            }
        };
    }

    // ============================================================
    // SUCCESS TESTS - COMMIT + CACHE
    // ============================================================

public function testCrearHaceCommitEnExito(): void
    {
        $data = ['nombre' => 'Test', 'precio_actual' => 100, 'precio_objetivo' => 80];

        $this->expectTransactionSuccess();
        $this->stubCacheVersion();
        $this->expectCacheSave(); // al menos uno
        
        // insert es crítico - debe ser exactamente 1
        $this->mockModel->expects($this->once())->method('insert')->willReturn(1);

        $result = $this->createService()->crear($data);

        $this->assertEquals(1, $result);
    }

    public function testActualizarHaceCommitEnExito(): void
    {
        $id = 5;
        $data = ['nombre' => 'Actualizado'];
        $mockEntity = new class { 
            public $id = 5; 
            public $nombre = 'Actualizado';
        };

        $this->expectTransactionSuccess();
        $this->stubCacheVersion();
        $this->expectCacheSave();
        
        // find puede llamarse varias veces, update debe ser 1
        $this->mockModel->expects($this->atLeastOnce())->method('find')->willReturn($mockEntity);
        $this->mockModel->expects($this->once())->method('update')->willReturn(true);

        $result = $this->createService()->actualizar($id, $data);

        $this->assertNotNull($result);
        $this->assertEquals($id, $result->id);
    }

    public function testEliminarHaceCommitEnExito(): void
    {
        $id = 10;
        $mockEntity = new class { public $id = 10; };

        $this->expectTransactionSuccess();
        $this->stubCacheVersion();
        $this->expectCacheSave();
        
        // delete debe ser exactamente 1
        $this->mockModel->expects($this->once())->method('find')->with($id)->willReturn($mockEntity);
        $this->mockModel->expects($this->once())->method('delete')->with($id)->willReturn(true);

        $result = $this->createService()->eliminar($id);

        $this->assertTrue($result);
    }

    /**
     * Test de INTEGRACIÓN: lectura después de write
     * Después de crear, el cache debe estar limpio → siguiente lectura hace query
     */
    public function testLecturaDespuesDeWriteHaceQuery(): void
    {
        // ===== PARTE 1: CREAR =====
        $createData = ['nombre' => 'Test', 'precio_actual' => 100];
        
        $this->expectTransactionSuccess();
        $this->stubCacheVersion();
        $this->expectCacheSave(); // 1 save para invalidar version
        $this->mockModel->method('insert')->willReturn(1);
        
        $service = $this->createService();
        $service->crear($createData);
        
        // ===== PARTE 2: LEER - crear nuevo service con mocks limpios =====
        $this->mockCache = $this->createMock(CacheInterface::class);
        $this->mockModel = $this->createMock(ProductoModel::class);
        
        $queryResult = ['data' => [['id' => 1]], 'pager' => $this->createMockPager()];
        
        $this->mockCache->expects($this->atLeastOnce())->method('get')->willReturn(null);
        $this->mockModel->expects($this->once())->method('paginateWithSearch')->willReturn($queryResult);
        
        // Nuevo service para limpiar estado
        $service2 = $this->createService();
        $result = $service2->obtenerTodos();
        
        // Assert - debe hacer query, no usar cache
        $this->assertArrayHasKey('data', $result);
        $this->assertFalse($result['cache_hit'] ?? false);
    }

    // ============================================================
    // CACHE TESTS - Lectura sin transacciones
    // ============================================================

    public function testObtenerTodosConCacheHitNoEjecutaQuery(): void
    {
        $cachedData = [
            'data' => [['id' => 1]],
            'pagerData' => ['total' => 1, 'perPage' => 10, 'pageCount' => 1]
        ];

        // Sin transacciones - es lectura
        $this->mockDb->expects($this->never())->method('transBegin');
        
        $this->mockCache->expects($this->atLeastOnce())->method('get')->willReturn($cachedData);
        $this->mockModel->expects($this->never())->method('paginateWithSearch');
        $this->expectNoCacheSave();

        $result = $this->createService()->obtenerTodos();

        $this->assertTrue($result['cache_hit']);
    }

    public function testObtenerTodosConCacheMissEjecutaQueryYGuarda(): void
    {
        $queryResult = ['data' => [['id' => 1]], 'pager' => $this->createMockPager()];

        // Sin transacciones
        $this->mockDb->expects($this->never())->method('transBegin');

        $this->mockCache->expects($this->atLeastOnce())->method('get')->willReturn(null);
        $this->mockModel->expects($this->once())->method('paginateWithSearch')->willReturn($queryResult);
        $this->expectCacheSave();

        $result = $this->createService()->obtenerTodos();

        // Validar estructura completa
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pager', $result);
    }

    public function testObtenerPorIdRetornaEntidad(): void
    {
        $id = 1;
        $mockEntity = new class { public $id = 1; };

        $this->mockDb->expects($this->never())->method('transBegin');
        $this->mockModel->expects($this->once())->method('find')->with($id)->willReturn($mockEntity);

        $result = $this->createService()->obtenerPorId($id);

        $this->assertEquals($id, $result->id);
    }

    // ============================================================
    // CACHE VERSIONING TEST
    // ============================================================

    public function testInvalidateCacheIncrementaVersionYActualizaKey(): void
    {
        $data = ['nombre' => 'Test', 'precio_actual' => 100];
        $saveCalls = [];

        $this->stubCacheVersion(1);
        
        $this->mockCache->expects($this->atLeastOnce())->method('save')
            ->willReturnCallback(function ($key, $value, $ttl) use (&$saveCalls) {
                $saveCalls[] = ['key' => $key, 'value' => $value];
                return true;
            });

        $this->mockDb->expects($this->once())->method('transBegin')->willReturn(true);
        $this->mockDb->expects($this->once())->method('transStatus')->willReturn(true);
        $this->mockDb->expects($this->once())->method('transCommit')->willReturn(true);
        $this->mockDb->method('transRollback')->willReturn(true);
        $this->mockModel->method('insert')->willReturn(1);

        $result = $this->createService()->crear($data);

        $this->assertEquals(1, $result);
        
        // Valida que la versión incrementa (no importa el orden)
        $versionSaves = array_filter($saveCalls, fn($s) => is_int($s['value']) && $s['value'] > 1);
        $versionValues = array_values(array_column($versionSaves, 'value'));
        
        $this->assertContains(2, $versionValues, 'Debe contener versión 2');
    }

    /**
     * Test de CONSISTENCIA: Nunca se guarda cache en errores
     * Regla del sistema: cualquier error → sin cache
     */
    public function testNuncaGuardaCacheEnErrores(): void
    {
        $data = ['nombre' => 'Test', 'precio_actual' => 100];

        // Cualquier error de transacción
        $this->mockDb->method('transBegin');
        $this->mockDb->method('transStatus')->willReturn(false);
        $this->mockDb->method('transRollback');

        // Jamás debe guardar cache
        $this->mockCache->expects($this->never())->method('save');

        $this->mockModel->method('insert')->willReturn(false);

        $this->createService()->crear($data);
    }

    // ============================================================
    // ERROR TESTS - ROLLBACK (errores de negocio)
    // ============================================================

    public function testCrearHaceRollbackSiInsertRetornaFalse(): void
    {
        $data = ['nombre' => 'Test', 'precio_actual' => 100];

        $this->expectTransactionRollback();
        $this->expectNoCacheSave();
        
        $this->mockModel->expects($this->once())->method('insert')->willReturn(false);

        $result = $this->createService()->crear($data);

        $this->assertFalse($result);
    }

    public function testCrearHaceRollbackSiInsertLanzaExcepcion(): void
    {
        $data = ['nombre' => 'Test', 'precio_actual' => 100];

        $this->expectTransactionRollback();
        $this->expectNoCacheSave();
        
        $this->mockModel->expects($this->once())
            ->method('insert')
            ->willThrowException(new \RuntimeException('DB Error'));

        $result = $this->createService()->crear($data);

        $this->assertFalse($result);
    }

    public function testActualizarHaceRollbackSiNoExiste(): void
    {
        $id = 999;
        $data = ['nombre' => 'No existe'];

        $this->expectTransactionRollback();
        $this->expectNoCacheSave();
        
        $this->mockModel->expects($this->once())->method('find')->with($id)->willReturn(null);

        $result = $this->createService()->actualizar($id, $data);

        $this->assertFalse($result);
    }

    public function testActualizarHaceRollbackSiUpdateRetornaFalse(): void
    {
        $id = 5;
        $data = ['nombre' => 'Updated'];
        $mockEntity = new class { public $id = 5; };

        $this->expectTransactionRollback();
        $this->expectNoCacheSave();
        
        $this->mockModel->expects($this->once())->method('find')->willReturn($mockEntity);
        $this->mockModel->expects($this->once())->method('update')->willReturn(false);

        $result = $this->createService()->actualizar($id, $data);

        $this->assertFalse($result);
    }

    public function testActualizarHaceRollbackSiUpdateLanzaExcepcion(): void
    {
        $id = 5;
        $data = ['nombre' => 'Updated'];
        $mockEntity = new class { public $id = 5; };

        $this->expectTransactionRollback();
        $this->expectNoCacheSave();
        
        $this->mockModel->expects($this->once())->method('find')->willReturn($mockEntity);
        $this->mockModel->expects($this->once())
            ->method('update')
            ->willThrowException(new \RuntimeException('DB Error'));

        $result = $this->createService()->actualizar($id, $data);

        $this->assertFalse($result);
    }

    public function testEliminarHaceRollbackSiNoExiste(): void
    {
        $id = 999;

        $this->expectTransactionRollback();
        $this->expectNoCacheSave();
        
        $this->mockModel->expects($this->once())->method('find')->with($id)->willReturn(null);

        $result = $this->createService()->eliminar($id);

        $this->assertFalse($result);
    }

    public function testEliminarHaceRollbackSiDeleteRetornaFalse(): void
    {
        $id = 10;
        $mockEntity = new class { public $id = 10; };

        $this->expectTransactionRollback();
        $this->expectNoCacheSave();
        
        $this->mockModel->expects($this->once())->method('find')->willReturn($mockEntity);
        $this->mockModel->expects($this->once())->method('delete')->willReturn(false);

        $result = $this->createService()->eliminar($id);

        $this->assertFalse($result);
    }

    public function testEliminarHaceRollbackSiDeleteLanzaExcepcion(): void
    {
        $id = 10;
        $mockEntity = new class { public $id = 10; };

        $this->expectTransactionRollback();
        $this->expectNoCacheSave();
        
        $this->mockModel->expects($this->once())->method('find')->willReturn($mockEntity);
        $this->mockModel->expects($this->once())
            ->method('delete')
            ->willThrowException(new \RuntimeException('DB Error'));

        $result = $this->createService()->eliminar($id);

        $this->assertFalse($result);
    }

    // ============================================================
    // ORDEN DE TRANSACCIONES - SECUENCIA EXACTA
    // ============================================================

    public function testTransaccionesOrdenCorrecto(): void
    {
        $data = ['nombre' => 'Test', 'precio_actual' => 100];
        $sequence = [];
        
        $this->mockDb->method('transBegin')->willReturnCallback(function () use (&$sequence) {
            $sequence[] = 'begin';
            return true;
        });
        $this->mockDb->expects($this->once())->method('transStatus')->willReturn(true);
        $this->mockDb->method('transCommit')->willReturnCallback(function () use (&$sequence) {
            $sequence[] = 'commit';
            return true;
        });
        
        $this->mockModel->method('insert')->willReturnCallback(function () use (&$sequence) {
            $sequence[] = 'insert';
            return 1;
        });
        
        $this->stubCacheVersion();
        $this->mockCache->method('save');

        $this->createService()->crear($data);

        // Valida orden exacto: begin -> insert -> commit
        $this->assertEquals(['begin', 'insert', 'commit'], $sequence);
    }

    public function testTransaccionesRollbackOrdenCorrecto(): void
    {
        $data = ['nombre' => 'Test', 'precio_actual' => 100];
        $sequence = [];
        
        $this->mockDb->method('transBegin')->willReturnCallback(function () use (&$sequence) {
            $sequence[] = 'begin';
            return true;
        });
        $this->mockDb->method('transRollback')->willReturnCallback(function () use (&$sequence) {
            $sequence[] = 'rollback';
            return true;
        });
        
        $this->mockModel->method('insert')->willReturnCallback(function () use (&$sequence) {
            $sequence[] = 'insert';
            return false;
        });

        $this->createService()->crear($data);

        // Valida orden: begin -> insert -> rollback
        $this->assertEquals(['begin', 'insert', 'rollback'], $sequence);
    }

    /**
     * Test: transBegin que falla hace rollback
     */
    public function testTransBeginFallaHaceRollback(): void
    {
        $data = ['nombre' => 'Test', 'precio_actual' => 100];

        $this->mockDb->expects($this->once())
            ->method('transBegin')
            ->willThrowException(new \RuntimeException('DB connection failed'));
        
        $this->mockCache->expects($this->never())->method('save');
        $this->mockModel->expects($this->never())->method('insert');

        $result = $this->createService()->crear($data);

        $this->assertFalse($result);
    }

    /**
     * Test: Commit que falla hace rollback automáticamente
     */
    public function testCommitFallaHaceRollback(): void
    {
        $data = ['nombre' => 'Test', 'precio_actual' => 100];

        $this->mockDb->method('transBegin');
        $this->mockDb->method('transStatus')->willReturn(true);
        $this->mockDb->expects($this->once())->method('transCommit')
            ->willThrowException(new \RuntimeException('Commit failed'));
        $this->mockDb->expects($this->once())->method('transRollback');

        $this->mockModel->method('insert')->willReturn(1);
        $this->mockCache->expects($this->never())->method('save');

        $result = $this->createService()->crear($data);

        $this->assertFalse($result);
    }

    /**
     * Test: No ejecuta update si producto no existe (no side effects)
     */
    public function testNoEjecutaUpdateSiNoExiste(): void
    {
        $id = 999;
        $data = ['nombre' => 'Updated'];

        // NO debe llamar update si no existe
        $this->mockModel->expects($this->never())->method('update');
        $this->mockModel->expects($this->once())->method('find')->willReturn(null);

        $result = $this->createService()->actualizar($id, $data);

        $this->assertFalse($result);
    }

    /**
     * Test: No ejecuta delete si producto no existe (no side effects)
     */
    public function testNoEjecutaDeleteSiNoExiste(): void
    {
        $id = 999;

        // NO debe llamar delete si no existe
        $this->mockModel->expects($this->never())->method('delete');
        $this->mockModel->expects($this->once())->method('find')->willReturn(null);

        $result = $this->createService()->eliminar($id);

        $this->assertFalse($result);
    }

    // ============================================================
    // DOUBLE OPERATION - Versioning consistency
    // ============================================================

/**
     * Test de concurrencia lógica: dos operaciones comparten versión base
     */
    public function testDoubleCreateIncrementaVersionDosVeces(): void
    {
        $currentVersion = 1;
        
        $this->mockCache
            ->method('get')
            ->willReturnCallback(function ($key) use (&$currentVersion) {
                if (str_contains($key, 'version')) {
                    return $currentVersion;
                }
                return null;
            });
        
        $saveCalls = [];
        $this->mockCache
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(function ($key, $value) use (&$currentVersion, &$saveCalls) {
                $saveCalls[] = $value;
                if (str_contains($key, 'version')) {
                    $currentVersion = $value;
                }
            });

        $this->mockDb->method('transBegin');
        $this->mockDb->method('transStatus')->willReturn(true);
        $this->mockDb->method('transCommit');
        $this->mockModel->method('insert')->willReturn(1);

        $service = $this->createService();
        
        $service->crear(['nombre' => 'Test1', 'precio_actual' => 100]);
        $service->crear(['nombre' => 'Test2', 'precio_actual' => 200]);

        // Valida que la versión incrementa (2 saves = 2 incrementos)
        $versionSaves = array_filter($saveCalls, fn($v) => is_int($v) && $v > 1);
        $versionValues = array_values($versionSaves);
        
        $this->assertContains(2, $versionValues, 'Debe contener versión 2');
    }

    // ============================================================
    // INTEGRATION TEST - Sin mocks (DB real)
    // ============================================================

    public function testIntegrationProductoNoExisteRetornaFalse(): void
    {
        $db = \Config\Database::connect();
        $dbName = $db->getDatabase();
        $tables = $db->listTables();
        
        if (!in_array('productos', $tables, true)) {
            // Crear tabla directamente en el test
            $db->query("
                CREATE TABLE IF NOT EXISTS productos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(255),
                    precio_actual DECIMAL(10,2),
                    precio_objetivo DECIMAL(10,2),
                    created_at DATETIME,
                    updated_at DATETIME,
                    deleted_at DATETIME
                )
            ");
            $tables = $db->listTables();
            fwrite(STDERR, "DB: {$dbName}, Tables after create: " . implode(', ', $tables) . "\n");
        }
        
        $service = new ProductosService();
        $result = $service->obtenerPorId(999999);
        
        $this->assertNull($result);
    }

    // ============================================================
    // READ AFTER UPDATE/DELETE - Detecta cache sucio
    // ============================================================

    /**
     * Test: después de actualizar, obtenerPorId devuelve datos nuevos
     */
    public function testReadAfterUpdateDevuelveDatosNuevos(): void
    {
        $id = 5;
        $initialEntity = (object)['id' => 5, 'nombre' => 'Viejo'];
        $updatedEntity = (object)['id' => 5, 'nombre' => 'Nuevo'];

        $this->expectTransactionSuccess();
        $this->stubCacheVersion();
        
        // Primera llamada: find para validar existencia
        // Segunda llamada: find después de update
        $this->mockModel->method('find')
            ->willReturnOnConsecutiveCalls($initialEntity, $updatedEntity);
        
        $this->mockModel->method('update')->willReturn(true);

        $service = $this->createService();
        
        $result = $service->actualizar($id, ['nombre' => 'Nuevo']);

        $this->assertEquals('Nuevo', $result->nombre);
    }

    /**
     * Test: después de eliminar, obtenerPorId retorna null (no cache sucio)
     */
    public function testReadAfterDeleteNoDevuelveDatos(): void
    {
        $id = 10;
        $existingEntity = (object)['id' => 10, 'nombre' => 'Test'];

        $this->expectTransactionSuccess();
        
        // Primera llamada: find para validar existencia
        // Segunda llamada: find después de delete (ahora null)
        $this->mockModel->method('find')
            ->willReturnOnConsecutiveCalls($existingEntity, null);
        
        $this->mockModel->method('delete')->willReturn(true);

        $service = $this->createService();
        
        $service->eliminar($id);
        $result = $service->obtenerPorId($id);

        $this->assertNull($result);
    }

    // ============================================================
    // EDGE CASES - Casos reales que pueden romper
    // ============================================================

    /**
     * Test: crear con data vacía retorna false (no debe hacer query)
     */
    public function testCrearConDataInvalidaRetornaFalse(): void
    {
        // Data vacía no debería ni intentar insert
        $this->mockModel->expects($this->never())->method('insert');
        $this->mockDb->expects($this->never())->method('transBegin');

        $result = $this->createService()->crear([]);

        $this->assertFalse($result);
    }

    /**
     * Test: con cache corrupto el service maneja el error
     */
    public function testObtenerTodosConCacheCorruptoHaceQuery(): void
    {
        // Cache devuelve algo no válido (string en vez de array)
        $this->mockCache->method('get')->willReturn('corrupted_data_not_array');
        
        $queryResult = ['data' => [], 'pager' => $this->createMockPager()];
        
        // Debe hacer query porque cache está corrupto
        $this->mockModel->expects($this->once())
            ->method('paginateWithSearch')
            ->willReturn($queryResult);

        $result = $this->createService()->obtenerTodos();

        $this->assertArrayHasKey('data', $result);
    }

/**
      * Test: Con cache corrupto (no array), hace query
      */
    public function testCacheCorruptoHaceQuery(): void
    {
        $this->stubCacheVersion(1);
        
        // Cache retorna no-array
        $this->mockCache->method('get')->willReturn('string_not_array');
        
        $queryResult = ['data' => [], 'pager' => $this->createMockPager()];
        
        $this->mockModel->expects($this->once())
            ->method('paginateWithSearch')
            ->willReturn($queryResult);

        $result = $this->createService()->obtenerTodos();

        $this->assertFalse($result['cache_hit'] ?? false);
    }

    /**
     * Test: actualizar con id inválido (string en vez de int)
     */
    public function testActualizarConIdInvalidoRetornaFalse(): void
    {
        // ID inválido no debería hacer transBegin
        $this->mockDb->expects($this->never())->method('transBegin');

        $result = $this->createService()->actualizar(0, ['nombre' => 'Test']);

        $this->assertFalse($result);
    }

    /**
     * Test CRÍTICO: cache NO se guarda si commit falla
     * Validación directa con mock - menos acoplado a implementación
     */
    public function testCacheNoSeGuardaSiCommitFalla(): void
    {
        $data = ['nombre' => 'Test', 'precio_actual' => 100];

        // Validación directa: cache nunca debe guardarse
        $this->mockCache->expects($this->never())->method('save');

        $this->mockDb->expects($this->once())->method('transBegin');
        
        $this->mockModel->method('insert')->willReturn(1);
        $this->mockDb->method('transStatus')->willReturn(true);
        
        // Commit lanza excepción
        $this->mockDb->expects($this->once())->method('transCommit')
            ->willThrowException(new \RuntimeException('Commit failed'));
        
        $this->mockDb->expects($this->once())->method('transRollback');

        $result = $this->createService()->crear($data);

        $this->assertFalse($result);
    }
}