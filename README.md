# Sistema de Seguimiento de Precios

Proyecto fullstack para gestión de productos con seguimiento de precios, detección de ofertas y optimización mediante cache.

El objetivo del proyecto es aprender arquitectura moderna con CodeIgniter 4 + Vue 3, aplicando separación de responsabilidades real entre capas, además de implementar observabilidad, testing, eventos y procesamiento asíncrono mediante colas.

---

# 🚀 Tecnologías utilizadas

## Backend

- PHP 8
- CodeIgniter 4
- MySQL
- Composer
- PHPUnit

## Frontend

- Vue 3
- TypeScript
- Axios
- Composition API

---

# 🧠 Idea del sistema

Un producto se considera en oferta cuando:

```php
precio_actual <= precio_objetivo
```

Esta lógica vive en el backend (Entity), no en el frontend.

---

# 🚀 Ejecución

## Backend

```bash
cd backend
composer install
php spark serve
```

Backend:

```txt
http://localhost:8080
```

---

## Frontend

```bash
cd frontend
npm install
npm run dev
```

Frontend:

```txt
http://localhost:5173
```

---

# 🏗️ Arquitectura general

```txt
HTTP Request
   ↓
Filters
   ↓
Controller
   ↓
Service
   ↓
Model
   ↓
Entity
   ↓
Transformer
   ↓
JSON Response
```

---

# 🔙 BACKEND (CodeIgniter 4)

---

# 📦 Arquitectura modular

El proyecto fue reorganizado utilizando módulos para mejorar:

- Escalabilidad
- Separación de responsabilidades
- Mantenibilidad
- Reutilización de componentes

Estructura principal:

```txt
Modules/
├── Productos/
├── Logs/
├── Core/
├── Auth/
```

Cada módulo encapsula:

- Controllers
- Services
- Models
- Entities
- Events
- Listeners
- Transformers

---

# 📡 Observabilidad del sistema (LOGS)

El sistema incluye logging estructurado en:

```txt
writable/logs
```

---

## 🧾 Request / Response logging

Se registra:

- Método HTTP
- Endpoint
- Status code
- Tiempo de respuesta
- Timestamp
- TRACE ID único

Ejemplo:

```txt
[REQUEST] GET /productos TRACE=abc123
[RESPONSE] 200 OK TRACE=abc123
```

---

## 🔍 TRACE ID

Cada request genera un identificador único:

```txt
TRACE=651f565129550c93
```

Esto permite rastrear todo el flujo:

```txt
REQUEST → CACHE → SQL → EVENTS → RESPONSE
```

---

# ⚡ Sistema de cache

El sistema implementa cache inteligente para optimizar queries repetidas.

Características:

- Cache por filtros
- TTL configurable
- Versionado de cache
- Invalidación automática
- Cache por paginación
- Cache por búsqueda

Ejemplos de logs:

```txt
CACHE | HIT
CACHE | MISS
CACHE | SAVE | ttl=60s
CACHE | INVALIDATED | v1→v2
```

---

# 🧩 SQL Logging

Se registran automáticamente:

- SELECT
- INSERT
- UPDATE
- DELETE
- Tiempo de ejecución SQL

Ejemplo:

```txt
SQL | time=0.23ms | SELECT * FROM productos
```

---

# 🔄 Transactions (Transacciones)

El sistema implementa transacciones para garantizar integridad de datos.

Cada operación importante:

- inicia transacción
- ejecuta operación
- hace COMMIT si todo sale bien
- hace ROLLBACK si ocurre un error

Ejemplo:

```txt
[TX] START | CREATE
[TX] COMMIT | CREATE
```

o en caso de error:

```txt
[TX] ROLLBACK | UPDATE
```

Esto evita inconsistencias en la base de datos.

---

# ⚙️ Event System + Queue System

El proyecto implementa un sistema de eventos desacoplado y procesamiento asíncrono.

---

## 🧠 ¿Cómo funciona?

Cuando ocurre una acción importante:

```txt
Crear producto
Actualizar producto
Eliminar producto
```

el sistema:

1. Ejecuta la transacción
2. Guarda datos en la base de datos
3. Hace COMMIT
4. Dispara un evento
5. El evento entra a una cola (`queue_jobs`)
6. Un worker procesa los jobs pendientes
7. Los listeners ejecutan tareas secundarias

---

## 🧩 Ejemplo real del flujo

```txt
POST /productos
   ↓
ProductosService
   ↓
INSERT producto
   ↓
COMMIT
   ↓
ProductoCreadoEvent
   ↓
QueueService
   ↓
queue_jobs
   ↓
queue:work
   ↓
Listeners
```

---

## 🎧 Listeners implementados

### RegistrarLogProductoListener

Registra logs automáticos del evento.

---

### InvalidarCacheProductoListener

Invalida cache automáticamente después de cambios.

---

### NotificarAlertaProductoListener

Detecta productos que entran en oferta.

---

# 🗂️ Queue Worker

El sistema incluye un worker CLI:

```bash
php spark queue:work
```

Responsabilidades:

- Buscar jobs pendientes
- Procesar eventos
- Ejecutar listeners
- Manejar errores
- Reintentos automáticos

---

## 🔁 Retry automático

Los jobs fallidos pueden reintentarse automáticamente:

```txt
attempts < max_attempts
```

Esto mejora resiliencia del sistema.

---

# 🧪 Testing

El proyecto incluye pruebas automatizadas utilizando PHPUnit.

---

## ✅ Tipos de pruebas realizadas

### Tests de servicios

Validan:

- creación
- actualización
- eliminación
- cache
- validaciones
- manejo de errores

---

### Tests de transactions

Se probaron escenarios como:

- rollback automático
- errores en update
- errores en delete
- transacciones exitosas

---

### Tests de cache

Validan:

- cache hit
- cache miss
- invalidación automática
- generación de keys

---

### Tests de eventos y cola

Se verificó:

- enqueue de jobs
- procesamiento de jobs
- ejecución de listeners
- manejo de fallos

---

# 🧠 Controller (ProductoController)

Responsable de:

- recibir requests
- validar input
- devolver responses
- delegar lógica al service

---

# ⚙️ Service (ProductosService)

Responsable de:

- lógica de negocio
- manejo de cache
- transactions
- dispatch de eventos
- coordinación del flujo del sistema

---

# 🗄️ Model (ProductoModel)

Responsable de acceso a base de datos.

Incluye:

- Soft deletes
- Query Builder
- Filtros dinámicos
- Paginación
- Búsqueda
- Ofertas

---

# 🧠 Entity (ProductoEntity)

Representa reglas reales del dominio.

Ejemplo:

```php
public function getEnOferta(): bool
{
    return (float) $this->precio_actual <= (float) $this->precio_objetivo;
}
```

---

# 🔄 Transformer (ProductoTransformer)

Responsable de:

- Entity → JSON
- Limpiar respuestas API
- Mantener contrato consistente

---

# 🌐 FRONTEND (Vue 3 + TypeScript)

---

# 🧩 Composables

Uso de:

- ref()
- computed()
- watch()

Responsabilidades:

- estado reactivo
- lógica reutilizable
- polling
- requests API

---

# 🚨 Sistema de alertas en tiempo real

El frontend implementa polling automático:

```txt
cada 5 segundos
```

para detectar nuevas ofertas.

Características:

- detección incremental
- evita duplicados
- sonido de alerta
- actualización automática
- control mediante timestamps (`since`)

---

# 🎨 Componentes Vue

## ProductList.vue

- v-for
- v-if
- props
- eventos
- filtros

---

## ProductForm.vue

- formularios reactivos
- validación
- edición/creación

---

## AlertasPrecio.vue

- polling automático
- render reactivo
- detección incremental

---

# 🌐 Axios

```ts
const api = axios.create({
  baseURL: "http://localhost:8080",
});
```

---

# ⚡ OPTIMIZACIONES IMPLEMENTADAS

✔ Cache con TTL  
✔ Cache versionado  
✔ Invalidación automática  
✔ Query optimization  
✔ Logging estructurado  
✔ Trace ID  
✔ Transactions  
✔ Queue system  
✔ Event-driven architecture  
✔ Retry automático  
✔ Polling incremental  
✔ Arquitectura modular  
✔ Soft deletes  
✔ Paginación optimizada  
✔ Testing automatizado

---

# 🧠 APRENDIZAJES

- Arquitectura por capas
- Arquitectura modular
- Event-driven architecture
- Queue systems
- Transactions
- Cache strategies
- Observabilidad backend
- Logging estructurado
- Optimización SQL
- Vue 3 Composition API
- Estado reactivo
- Polling en frontend
- Testing automatizado
- Separación de responsabilidades
- PHP moderno con Composer
- CodeIgniter 4 avanzado

---

# 📚 Aprendizaje continuo

Durante el desarrollo del proyecto también se reforzaron conocimientos mediante:

- documentación oficial de PHP
- documentación oficial de CodeIgniter 4
- tutoriales de arquitectura backend
- prácticas de clean architecture
- testing y patrones de diseño

El proyecto fue utilizado como entorno práctico de aprendizaje y experimentación de arquitectura moderna fullstack.
