# Sistema de Seguimiento de Precios

Proyecto fullstack para gestión de productos con seguimiento de precios, detección de ofertas y optimización mediante cache.

El objetivo del proyecto es aprender arquitectura moderna con CodeIgniter 4 + Vue 3, aplicando separación de responsabilidades real entre capas, además de implementar un sistema de observabilidad con logs de request, SQL y cache.

---

## 🧠 Idea del sistema

Un producto se considera en oferta cuando:

precio_actual <= precio_objetivo

Esta lógica vive en el backend (Entity), no en el frontend.

---

## 🚀 Ejecución

### Backend

cd backend  
php spark serve

http://localhost:8080

### Frontend

cd frontend  
npm install  
npm run dev

http://localhost:5173

---

## 🏗️ Arquitectura general

1. HTTP Request
2. Filter (RequestLogFilter + Trace ID)
3. Controller
4. Service (lógica de negocio + cache)
5. Model (acceso a datos SQL)
6. Entity (reglas de dominio)
7. Transformer (formato API)
8. JSON Response

---

# 🔙 BACKEND (CodeIgniter 4)

---

## 📡 Observabilidad del sistema (LOGS)

El sistema incluye logging estructurado en `writable/logs`:

### 🧾 Request / Response logging

- Método HTTP
- Endpoint
- Status code
- Timestamp
- TRACE ID por request

---

### 🧠 Cache logging

El sistema registra:

- CACHE HIT (respuesta desde cache, sin SQL)
- CACHE MISS (ejecución de SQL)
- CACHE SAVE (guardado con TTL)
- CACHE INVALIDATED (cuando se crea/actualiza/elimina producto)

Ejemplo:

CACHE | HIT | key=productos_v2_xxx  
CACHE | MISS | key=productos_v2_xxx  
CACHE | SAVE | ttl=60s  
CACHE | INVALIDATED | oldVersion=1 → newVersion=2

---

### 🧩 SQL logging

Se registran todas las consultas:

- Tiempo de ejecución (ms)
- Query ejecutada
- SELECT / INSERT / UPDATE / DELETE

Ejemplo:

SQL | time=0.23ms | SELECT \* FROM productos

---

### 🔍 TRACE ID

Cada request genera un identificador único:

TRACE=651f565129550c93

Esto permite rastrear todo el flujo:

REQUEST → CACHE → SQL → RESPONSE

---

## 🧠 Controller (ProductoController)

Responsable de:

- Recibir requests
- Validar input
- Delegar al service
- Formatear respuesta API

---

## ⚙️ Service (ProductosService)

Herramientas usadas:

- model('ProductoModel')
- service('cache')

Responsabilidades:

- Manejo de cache (GET / SAVE / INVALIDATE)
- Generación de cache keys
- Control de versión de cache
- Lógica de negocio de productos

---

### 🧠 Sistema de cache

El sistema implementa:

- Cache por filtros (query + paginación + ofertas)
- TTL de 60 segundos
- Versionado de cache (evita invalidaciones masivas costosas)
- Invalidación automática al crear/actualizar/eliminar

---

## 🗄️ Model (ProductoModel)

Responsable de acceso a base de datos.

Incluye:

- Soft deletes
- Query builder dinámico
- Filtros por búsqueda
- Filtro de ofertas
- Paginación manual optimizada

---

## 🧠 Entity (ProductoEntity)

class ProductoEntity extends Entity

Regla de negocio principal:

public function getEnOferta(): bool  
{  
 return (float) $this->precio_actual <= (float) $this->precio_objetivo;  
}

Responsabilidad:

- Contiene lógica de dominio
- No depende del frontend
- Representa entidad real del sistema

---

## 🔄 Transformer (ProductoTransformer)

Responsable de:

- Convertir Entity → JSON API
- Evitar exposición de datos innecesarios
- Mantener contrato de API limpio

---

## 🌐 FRONTEND (Vue 3 + TypeScript)

---

## 🧩 Composables (useProducto)

Uso de:

- ref()
- computed()
- watch()

Responsabilidad:

- Estado centralizado de productos
- Reutilización de lógica
- Manejo de requests

---

## 🎨 Componentes

### ProductList.vue

- v-for
- v-if
- computed
- props

### ProductCard.vue

- props
- render condicional
- bindings dinámicos

---

## 🌐 Axios

const api = axios.create({  
 baseURL: "http://localhost:8080"  
});

---

## 🧠 SISTEMA COMPLETO

Controller → HTTP  
Service → Cache + lógica  
Model → SQL  
Entity → dominio  
Transformer → API

---

## ⚡ OPTIMIZACIONES IMPLEMENTADAS

✔ Cache con TTL (60s)  
✔ Cache versionado  
✔ Evita queries repetidas  
✔ SQL logging con tiempo de ejecución  
✔ Request tracing con ID único  
✔ Invalidación automática de cache

---

## 🧠 APRENDIZAJES

- Arquitectura por capas real
- Separación de responsabilidades
- Cache strategy con invalidación inteligente
- Observabilidad backend (logs completos)
- Optimización de queries SQL
- Vue 3 Composition API
- Estado reactivo con composables
