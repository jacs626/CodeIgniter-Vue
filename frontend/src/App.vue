<script setup lang="ts">
import { ref, onMounted } from "vue";

interface Producto {
  id: number;
  nombre: string;
  precio_actual: number;
  precio_objetivo: number;
}

const nuevoProducto = ref({
  nombre: "",
  precio_actual: 0,
  precio_objetivo: 0,
});

const editando = ref(false);
const productoEditandoId = ref<number | null>(null);

const productos = ref<Producto[]>([]);

const crearProducto = async () => {
  try {
    await fetch("http://localhost:8080/productos", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(nuevoProducto.value),
    });

    nuevoProducto.value = {
      nombre: "",
      precio_actual: 0,
      precio_objetivo: 0,
    };

    obtenerProductos();
  } catch (error) {
    console.error("Error al crear producto:", error);
  }
};

const obtenerProductos = async () => {
  try {
    const res = await fetch("http://localhost:8080/productos");
    const data = await res.json();
    productos.value = data;
  } catch (error) {
    console.error("Error al obtener productos:", error);
  }
};

const eliminarProducto = async (id: number) => {
  if (!confirm("¿Estás seguro de eliminar este producto?")) return;

  try {
    await fetch(`http://localhost:8080/productos/${id}`, {
      method: "DELETE",
    });

    obtenerProductos();
  } catch (error) {
    console.error("Error al eliminar:", error);
  }
};

const cargarProducto = (producto: Producto) => {
  nuevoProducto.value = { ...producto };
  productoEditandoId.value = producto.id;
  editando.value = true;
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const actualizarProducto = async () => {
  if (!productoEditandoId.value) return;

  try {
    await fetch(`http://localhost:8080/productos/${productoEditandoId.value}`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(nuevoProducto.value),
    });

    editando.value = false;
    productoEditandoId.value = null;
    nuevoProducto.value = {
      nombre: "",
      precio_actual: 0,
      precio_objetivo: 0,
    };

    obtenerProductos();
  } catch (error) {
    console.error("Error al actualizar:", error);
  }
};

const cancelarEdicion = () => {
  editando.value = false;
  productoEditandoId.value = null;
  nuevoProducto.value = {
    nombre: "",
    precio_actual: 0,
    precio_objetivo: 0,
  };
};

onMounted(() => {
  obtenerProductos();
});
</script>

<template>
  <div class="container">
    <header class="header">
      <h1>Gestión de Precios</h1>
    </header>

    <main class="main-content">
      <section class="form-section">
        <h2>{{ editando ? "Editar Producto" : "Nuevo Producto" }}</h2>
        <form @submit.prevent="editando ? actualizarProducto() : crearProducto()">
          <div class="form-group">
            <label for="nombre">Nombre</label>
            <input
              id="nombre"
              v-model="nuevoProducto.nombre"
              placeholder="Ingresa el nombre del producto"
              required
            />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="precio_actual">Precio Actual</label>
              <input
                id="precio_actual"
                v-model.number="nuevoProducto.precio_actual"
                type="number"
                step="0.01"
                placeholder="0.00"
                required
              />
            </div>

            <div class="form-group">
              <label for="precio_objetivo">Precio Objetivo</label>
              <input
                id="precio_objetivo"
                v-model.number="nuevoProducto.precio_objetivo"
                type="number"
                step="0.01"
                placeholder="0.00"
                required
              />
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              {{ editando ? "Actualizar" : "Crear" }}
            </button>
            <button
              v-if="editando"
              type="button"
              class="btn btn-secondary"
              @click="cancelarEdicion"
            >
              Cancelar
            </button>
          </div>
        </form>
      </section>

      <section class="list-section">
        <h2>Productos ({{ productos.length }})</h2>
        <div v-if="productos.length === 0" class="empty-state">
          No hay productos registrados
        </div>
        <div v-else class="product-grid">
          <article v-for="producto in productos" :key="producto.id" class="product-card">
            <div class="product-info">
              <h3>{{ producto.nombre }}</h3>
              <div class="prices">
                <div class="price-item">
                  <span class="label">Actual</span>
                  <span class="value">${{ Number(producto.precio_actual).toFixed(2) }}</span>
                </div>
                <div class="price-item">
                  <span class="label">Objetivo</span>
                  <span class="value">${{ Number(producto.precio_objetivo).toFixed(2) }}</span>
                </div>
              </div>
            </div>
            <div class="product-actions">
              <button class="btn btn-edit" @click="cargarProducto(producto)">
                Editar
              </button>
              <button class="btn btn-delete" @click="eliminarProducto(producto.id)">
                Eliminar
              </button>
            </div>
          </article>
        </div>
      </section>
    </main>
  </div>
</template>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
    Ubuntu, Cantarell, sans-serif;
  background: #f5f7fa;
  color: #2c3e50;
  min-height: 100vh;
}

.container {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px;
}

.header {
  text-align: center;
  padding: 30px 0;
  margin-bottom: 20px;
}

.header h1 {
  font-size: 2rem;
  color: #1a1a2e;
}

.main-content {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.form-section,
.list-section {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

h2 {
  font-size: 1.1rem;
  margin-bottom: 20px;
  color: #4a4a68;
  font-weight: 600;
}

.form-group {
  margin-bottom: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

label {
  display: block;
  margin-bottom: 6px;
  font-size: 0.875rem;
  color: #666;
  font-weight: 500;
}

input {
  width: 100%;
  padding: 12px 14px;
  border: 2px solid #e8e8e8;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

input:focus {
  outline: none;
  border-color: #667eea;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5568d3;
}

.btn-secondary {
  background: #e8e8e8;
  color: #666;
}

.btn-secondary:hover {
  background: #ddd;
}

.btn-edit {
  background: #48bb78;
  color: white;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 0.875rem;
}

.btn-edit:hover {
  background: #38a169;
}

.btn-delete {
  background: #f56565;
  color: white;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 0.875rem;
}

.btn-delete:hover {
  background: #e53e3e;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #999;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

.product-card {
  background: #fafafa;
  border-radius: 10px;
  padding: 18px;
  border: 1px solid #e8e8e8;
}

.product-info h3 {
  font-size: 1.05rem;
  margin-bottom: 12px;
  color: #2c3e50;
}

.prices {
  display: flex;
  gap: 20px;
  margin-bottom: 16px;
}

.price-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.price-item .label {
  font-size: 0.75rem;
  color: #999;
}

.price-item .value {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2c3e50;
}

.product-actions {
  display: flex;
  gap: 10px;
}
</style>