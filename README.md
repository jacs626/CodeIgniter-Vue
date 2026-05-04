# Sistema de Seguimiento de Precios

Proyecto fullstack para gestion de productos con seguimiento de precios y ofertas.

## Tecnologias

| Capa         | Framework          | Version |
| ------------ | ------------------ | ------- |
| **Frontend** | Vue 3 + TypeScript | 3.5.x   |
| **Build**    | Vite               | 8.x     |
| **HTTP**     | Axios              | 1.16.x  |
| **Backend**  | CodeIgniter        | 4.7.x   |
| **DB**       | MySQL/MariaDB      | 9.7     |

---

## Ejecucion

### Backend

```bash
cd backend
php spark serve
# http://localhost:8080
```

### Frontend

```bash
cd frontend
npm run dev
# http://localhost:5173
```

---

## Estructura

```
Proyectos/
├── backend/
│   ├── app/
│   │   ├── Config/          # Configuraciones (Routes, Services, CORS)
│   │   ├── Controllers/      # Controladores REST
│   │   ├── Models/          # Modelos con hooks
│   │   ├── Services/       # Logica de negocio
│   │   ├── Database/       # Migrations + Seeds
│   │   └── Validation/     # Reglas de validacion
│   └── system/             # CodeIgniter core
│
└── frontend/
    ├── src/
    │   ├── components/    # Componentes Vue
    │   ├── composables/    # Logica reutilizable
    │   ├── types/         # TypeScript interfaces
    │   └── styles/        # CSS global
    └── public/
```

---

## ARQUITECTURA BACKEND (CodeIgniter 4)

### Flujo de una peticion

```
HTTP Request
    │
Routes (app/Config/Routes.php)
    │
Filter (CORS) (app/Config/Filters.php)
    │
Controller (ProductoController)
    │
Service (ProductosService)
    │
Model (ProductoModel)
    │
Database
```

### 1. Controller - Capa de Recepcion

**Archivo:** `backend/app/Controllers/ProductoController.php`

```php
class ProductoController extends ResourceController
{
    // Recibe requests HTTP
    // Valida datos
    // Delega a Service
    // Retorna JSON

    public function index()      // GET /productos
    public function create()   // POST /productos
    public function update()    // PUT /productos/:id
    public function delete()    // DELETE /productos/:id
}
```

**Herramientas CodeIgniter utilizadas:**

- `ResourceController` - RESTful base controller
- `$this->request->getJSON()` - Parse JSON body
- `$this->request->getGet('q')` - Query params
- `$this->validate()` - Validacion integrada
- `$this->respond()` - Respuesta JSON

### 2. Service - Logica de Negocio

**Archivo:** `backend/app/Services/ProductosService.php`

```php
class ProductosService
{
    protected $model;

    // Abstrae el acceso a datos
    // Contiene logica de negocio
    // No conoce HTTP

    public function obtenerTodos(?string $q = null)
    public function obtenerPorId(int $id)
    public function crear(array $data)
    public function actualizar(int $id, array $data)
    public function eliminar(int $id)
}
```

**Herramientas CodeIgniter utilizadas:**

- `model('ProductoModel')` - Inyeccion de Model via Service
- Registro en `app/Config/Services.php`

### 3. Entity - Representacion de Datos

**Archivo:** `backend/app/Entities/ProductoEntity.php`

```php
use CodeIgniter\Entity\Entity;

class ProductoEntity extends Entity
{
    // Campos que vienen de la base de datos
    protected $attributes = [
        'id'            => null,
        'nombre'        => null,
        'precio_actual' => null,
        'precio_objetivo'=> null,
        'en_oferta'     => null,
    ];

    // Conversion automatica de tipos
    protected $casts = [
        'id'            => 'int',
        'precio_actual' => 'float',
        'precio_objetivo'=> 'float',
        'en_oferta'     => 'bool',
    ];

    // Logica de negocio encapsulada
    public function getEnOferta(): bool
    {
        return (float) ($this->precio_actual ?? 0) <= (float) ($this->precio_objetivo ?? 0);
    }
}
```

**Herramientas CodeIgniter utilizadas:**

- `CodeIgniter\Entity\Entity` - Base Entity
- `$attributes` - Mapeo de campos BD
- `$casts` - Conversion automatica de tipos
- `getEnOferta()` - Getter para logica de negocio

### 4. Model - Acceso a Datos

**Archivo:** `backend/app/Models/ProductoModel.php`

```php
use App\Entities\ProductoEntity;

class ProductoModel extends Model
{
    protected $table = 'productos';
    protected $useSoftDeletes = true;

    // Retorna objetos Entity en lugar de arrays
    protected $returnType = ProductoEntity::class;

    // Hook que usa la logica de la Entity
    protected $afterFind = ['setEnOferta'];

    protected function setEnOferta(array $data): array
    {
        $productos = is_array($data['data']) ? $data['data'] : [$data['data']];

        foreach ($productos as $producto) {
            if ($producto instanceof ProductoEntity) {
                // Usa el metodo de la Entity
                $producto->en_oferta = $producto->getEnOferta();
            }
        }

        return $data;
    }
}
```

**Herramientas CodeIgniter utilizadas:**

- `CodeIgniter\Model` - Base Model
- `$returnType` - Tipo de retorno (Entity)
- `$afterFind` - Hook despues de cada SELECT
- `useSoftDeletes` - Soft delete automatico
- `useTimestamps` - created_at/updated_at automaticos
- `allowedFields` - Whitelist de campos

### 5. Routes - Endpoint Definitions

**Archivo:** `backend/app/Config/Routes.php`

```php
$routes->resource('productos', ['controller' => 'ProductoController']);
// Genera automaticamente:
// GET    /productos        → index()
// POST   /productos        → create()
// GET    /productos/:id    → show()
// PUT    /productos/:id    → update()
// DELETE /productos/:id    → delete()
```

### 6. Service Registration

**Archivo:** `backend/app/Config/Services.php`

```php
public static function productoService($getShared = true)
{
    if ($getShared) {
        return static::getSharedInstance('productoService');
    }
    return new \App\Services\ProductosService();
}
```

**Uso en Controller:**

```php
$this->service = service('productoService');
```

### 6. CORS Configuration

**Archivo:** `backend/app/Config/Cors.php`

```php
public array $default = [
    'allowedOrigins' => ['http://localhost:5173'],
    'allowedMethods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
    'allowedHeaders' => ['Content-Type'],
];
```

---

## ARQUITECTURA FRONTEND (Vue 3 + TypeScript)

### Flujo de Datos

```
User Action
    │
Component (ProductList.vue)
    │
Composable (useProducto.ts)
    │
API Call (axios)
    │
Backend REST API
    │
Reactive Update (ref + watch)
    │
UI Update
```

### 1. Tipos TypeScript

**Archivo:** `frontend/src/types/index.ts`

```typescript
export interface Producto {
  id: number;
  nombre: string;
  precio_actual: string | number;
  precio_objetivo: string | number;
  en_oferta?: boolean;
}

export interface ProductoForm {
  nombre: string;
  precio_actual: number;
  precio_objetivo: number;
}
```

### 2. Composable - Logica de Estado

**Archivo:** `frontend/src/composables/useProducto.ts`

```typescript
export function useProducto() {
  const productos = ref<Producto[]>([]);     // Estado reactivo
  const loading = ref(false);
  const error = ref<string | null>(null);
  const searchQuery = ref('');

  // Watcher con debounce para busqueda
  watch(searchQuery, () => {
    debounceTimer = setTimeout(() => {
      obtenerProductos();
    }, 300);
  });

  // Metodos API
  const obtenerProductos = async () => { ... }
  const crearProducto = async (data: ProductoForm) => { ... }
  const actualizarProducto = async (id: number, data: ProductoForm) => { ... }
  const eliminarProducto = async (id: number) => { ... }

  return { productos, loading, error, searchQuery, ... };
}
```

**Herramientas Vue utilizadas:**

- `ref()` - Estado reactivo primitivo
- `watch()` - Reaccion a cambios
- `computed()` - Propiedades derivadas (en ProductList)

### 3. Componentes Vue

#### ProductList.vue - Filtros y Grid

```vue
<script setup lang="ts">
const props = defineProps<{
  productos: Producto[];
  searchQuery: string;
  onlyOffers?: boolean;
}>();

// Computed para filtrar localmente
const filteredProducts = computed(() => {
  let result = props.productos;
  if (props.onlyOffers) {
    result = result.filter((p) => p.en_oferta);
  }
  return result;
});
</script>

<template>
  <!-- Input con v-model:searchQuery -->
  <!-- Checkbox con v-model:onlyOffers -->
  <!-- Grid de ProductCard con v-for -->
</template>
```

**Herramientas Vue utilizadas:**

- `defineProps()` - Props tipadas
- `defineEmits()` - Eventos
- `computed()` - Propiedad derivada
- `v-model` - Two-way binding

#### ProductCard.vue - Visualizacion

```vue
<template>
  <article class="product-card" :class="{ 'en-oferta': producto.en_oferta }">
    <div class="product-header">
      <h3>{{ producto.nombre }}</h3>
      <span v-if="producto.en_oferta" class="badge-oferta">🔥</span>
    </div>
  </article>
</template>
```

**Herramientas Vue utilizadas:**

- `v-if` - Renderizado condicional
- `v-for` - Renderizado de listas
- `:key` - Identificador unico
- `:class` - Clases dinamicas

### 4. Axios - Cliente HTTP

```typescript
const api = axios.create({
  baseURL: "http://localhost:8080",
});

// Interceptor para extraer data automaticamente
api.interceptors.response.use(
  (response) => response.data,
  (error) => Promise.reject(error.response.data),
);
```

---

## CARACTERISTICAS IMPLEMENTADAS

| Feature             | Backend                | Frontend         |
| ------------------- | ---------------------- | ---------------- |
| CRUD productos      | Controller + Service   | Axios calls      |
| Campo `en_oferta`   | Model hook `afterFind` | Visual 🔥        |
| Busqueda por nombre | Query param `?q=`      | Watch + debounce |
| Filtro ofertas      | -                      | `computed`       |
| Soft deletes        | Model `useSoftDeletes` | DELETE endpoint  |

---

## HERRAMIENTAS CLAVE POR PROYECTO

### CodeIgniter 4

| Herramienta           | Uso                     |
| --------------------- | ---------------------- |
| `ResourceController`  | Base RESTful           |
| `Model::afterFind`    | Hook campo calculado   |
| `Entity`             | Objeto representan datos |
| `$returnType`         | Tipo de retorno Model |
| `model()` helper      | Inyeccion Model      |
| `service()` helper   | Inyeccion Service    |
| `$routes->resource()`| Auto-rutas REST       |
| `useSoftDeletes`     | Soft delete           |
| `useTimestamps`      | Fechas automaticas   |

### Vue 3

| Herramienta     | Uso                     |
| --------------- | ----------------------- |
| `ref()`         | Estado reactivo         |
| `watch()`       | Reaccion a cambios      |
| `computed()`    | Propiedades derivadas   |
| `defineProps()` | Props tipadas           |
| `defineEmits()` | Eventos                 |
| `v-model`       | Two-way binding         |
| `v-if`          | Renderizado condicional |
| `v-for`         | Renderizado de listas   |
| `:key`          | Identificador unico     |

---

## VERIFICACIONES

```bash
# Frontend
cd frontend
npm run lint        # Oxlint + ESLint
npm run type-check # vue-tsc

# Backend
cd backend
php spark migrate:status # Ver migrations
php spark serve        # Iniciar server
```

---

## APRENDIZAJE - CONCEPTOS CLAVE

### Por que Separacion de Responsabilidades?

```
Controller  → No logica de datos, solo HTTP
Service    → Logica de negocio, abstraccion del Model
Model      → Solo acceso a datos + hooks
```

**Beneficio:** Facil de testear y mantener.

### Por que Model Hook?

```php
//antes: logica en frontend
$producto->en_oferta = ($precio <= $objetivo);

//ahora: logica en backend
protected $afterFind = ['setEnOferta'];
// El campo se calcula automaticamente en CADA query
```

**Beneficio:** El campo siempre existe en la respuesta API.

### Por que Entities?

```
Array          → Solo datos (clave-valor)
Entity         → Datos + metodos (logica encapsulada)

$producto['en_oferta']     // array
$producto->getEnOferta()   // entity con logica
$producto->en_oferta       // property con valor calculado
```

**Beneficio:**
- Logica de negocio junto a los datos
- Tipos automaticos con $casts
- Codigo mas legible y mantenible

### Por que Composables?

```typescript
// Malo: logica en cada componente
// const producto1 = ref([]) en ComponentA
// const producto2 = ref([]) en ComponentB

// Bueno: composable compartido
const { productos } = useProducto(); // Reutilizable
```

**Beneficio:** Estado compartido, codigo reutilizable.
