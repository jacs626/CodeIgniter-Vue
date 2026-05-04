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

HTTP Request
↓
Controller
↓
Service (lógica de negocio)
↓
Model (acceso a datos)
↓
Entity (reglas de dominio)
↓
Transformer (formato API)
↓
JSON Response

---

# 🔙 BACKEND (CodeIgniter 4)

## Controller (ProductoController)

class ProductoController extends ResourceController

Herramientas usadas:

- ResourceController → CRUD REST automático
- service('productoService') → inyección de servicio
- respond() → respuesta HTTP estándar
- failNotFound() / failValidationErrors() → manejo de errores

Rol: solo recibe requests y delega lógica.

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
- allowedFields → campos permitidos
- useSoftDeletes → eliminación lógica
- returnType → retorna Entity

Rol: solo datos, sin lógica de negocio.

---

## Entity (ProductoEntity)

class ProductoEntity extends Entity

Herramientas usadas:

- $attributes → estructura del objeto
- $casts → conversión automática de tipos

Lógica de negocio:

public function getEnOferta(): bool
{
return (float) $this->precio_actual <= (float) $this->precio_objetivo;
}

Rol:

- contiene reglas del dominio
- representa el producto como objeto real

---

## Transformer (ProductoTransformer)

Herramientas usadas:

- transform() → Entity a array
- array_map() → listas

Ejemplo:

return [
'id' => $producto->id,
'nombre' => $producto->nombre,
'precio_actual' => $producto->precio_actual,
'precio_objetivo' => $producto->precio_objetivo,
'en_oferta' => $producto->getEnOferta(),
];

Rol:
controla la forma final del JSON de la API

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
- computed() → datos derivados

Rol:
centraliza lógica de productos

---

## Componentes

ProductList.vue:

- defineProps()
- v-model
- v-for
- v-if
- computed()

ProductCard.vue:

- props
- v-if
- :class

---

## Axios

const api = axios.create({
baseURL: "http://localhost:8080",
});

---

# 🧩 ARQUITECTURA

Controller → HTTP  
Service → lógica de negocio  
Model → datos  
Entity → dominio  
Transformer → API

---

## Entity vs Model

Model → datos  
Entity → lógica

La lógica NO está en frontend ni en controller.

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
- transformación de API
- Vue 3 Composition API
- estado reactivo con ref/watch/computed
