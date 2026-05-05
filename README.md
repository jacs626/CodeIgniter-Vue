# Sistema de Seguimiento de Precios

Proyecto fullstack para gestión de productos con seguimiento de precios y detección de ofertas.

El objetivo del proyecto es aprender arquitectura moderna con CodeIgniter 4 + Vue 3, aplicando separación de responsabilidades real entre capas.

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

# http://localhost:8080

### Frontend

cd frontend  
npm install  
npm run dev

# http://localhost:5173

---

## 🏗️ Arquitectura general

1. HTTP Request
2. Controller
3. Service (lógica de negocio)
4. Model (acceso a datos + filtros + paginación)
5. Entity (reglas de dominio)
6. Transformer (formato API)
7. JSON Response

---

# 🔙 BACKEND (CodeIgniter 4)

## Controller (ProductoController)

class ProductoController extends ResourceController

Herramientas usadas:

- ResourceController → CRUD REST automático
- service('productoService') → inyección de servicio
- respond() → respuesta HTTP estándar
- failNotFound() / failValidationErrors() → manejo de errores
- validation service → validación de datos

Rol: recibe requests, valida datos y delega lógica.

---

## Service (ProductosService)

Herramientas usadas:

- model('ProductoModel') → acceso al modelo

Rol:

- crear productos
- actualizar productos
- eliminar productos
- buscar productos

---

## Model (ProductoModel)

protected $returnType = ProductoEntity::class;

Herramientas CodeIgniter usadas:

- Model → acceso a base de datos
- Query Builder → filtros dinámicos (LIKE, WHERE)
- allowedFields → campos permitidos
- useSoftDeletes → eliminación lógica
- returnType → retorna Entity

Ejemplo filtros:

```php
if ($search) {
    $builder->like('nombre', $search);
}

if ($soloOfertas) {
    $builder->where('precio_actual <= precio_objetivo');
}
```

Rol: acceso a datos + construcción de consultas.

---

## Entity (ProductoEntity)

class ProductoEntity extends Entity

Herramientas usadas:

- $attributes → estructura del objeto
- $casts → conversión automática de tipos

Lógica de negocio:

```php
public function getEnOferta(): bool
{
    return (float) $this->precio_actual <= (float) $this->precio_objetivo;
}
```

Rol:

- contiene reglas del dominio
- representa el producto como objeto real

---

## Transformer (ProductoTransformer)

Herramientas usadas:

- transform() → Entity a array
- array_map() → listas

Ejemplo:

```php
return [
    'id' => $producto->id,
    'nombre' => $producto->nombre,
    'precio_actual' => $producto->precio_actual,
    'precio_objetivo' => $producto->precio_objetivo,
    'en_oferta' => $producto->getEnOferta(),
];
```

Rol: controla la forma final del JSON de la API.

---

## ✅ Validación (CodeIgniter 4)

La validación está centralizada en:

app/Config/Validation.php

Se separa en:

- producto_create
- producto_update

Herramientas usadas:

- required
- decimal
- greater_than
- min_length / max_length
- permit_empty (para updates parciales)

---

## 🧩 Custom Rule

Archivo:

app/Validation/CustomRules.php

```php
public function precioLogico(string $value, string $params, array $data): bool
{
    if (!isset($data['precio_objetivo'])) {
        return true;
    }

    return (float) $value <= ((float) $data['precio_objetivo'] * 10);
}
```

Rol:

- valida consistencia entre campos
- evita valores irreales

---

## 🔍 Filtros y búsqueda

Los filtros se ejecutan en el backend:

- búsqueda por nombre (LIKE)
- filtro de ofertas (precio_actual <= precio_objetivo)

Antes:

Frontend filtraba datos

Ahora:

Backend filtra → frontend solo renderiza

---

## 📄 Paginación

Se implementó paginación en backend con metadata:

```json
{
  "currentPage": 1,
  "perPage": 10,
  "total": 100,
  "pageCount": 10
}
```

Rol:

- evitar inconsistencias
- soportar filtros correctamente

---

## Routes

$routes->resource('productos');

Genera:

- GET /productos
- GET /productos/{id}
- POST /productos
- PUT /productos/{id}
- DELETE /productos/{id}

---

# 🌐 FRONTEND (Vue 3 + TypeScript)

## Composables (useProducto)

Herramientas Vue usadas:

- ref() → estado reactivo
- watch() → cambios
- axios → conexión API

Ejemplo:

```ts
const productos = ref([]);
const searchQuery = ref("");

watch(searchQuery, () => {
  setTimeout(() => {
    obtenerProductos();
  }, 300);
});
```

Rol: centraliza lógica y llamadas al backend.

---

## Componentes

ProductList.vue:

- defineProps()
- v-model
- v-for
- v-if

ProductCard.vue:

- props
- v-if
- :class

---

## Axios

```ts
const api = axios.create({
  baseURL: "http://localhost:8080",
});
```

---

# 🧩 ARQUITECTURA

Controller → HTTP  
Service → lógica de negocio  
Model → datos + queries  
Entity → dominio  
Transformer → API

---

## Entity vs Model

Model → datos  
Entity → lógica

La lógica NO está en frontend ni en controller.

---

## Validación

- centralizada en Config
- reutilizable
- soporta reglas complejas

---

## Transformer

- controla JSON
- evita exponer datos innecesarios
- desacopla API

---

## Vue Composables

- reutilización de lógica
- estado centralizado
- evita duplicación

---

# 🧠 APRENDIZAJES

- arquitectura por capas real
- separación de responsabilidades
- Entity como dominio
- validación avanzada en backend
- filtros en SQL en lugar de frontend
- manejo de estado reactivo con ref/watch
- evitar lógica duplicada frontend/backend
