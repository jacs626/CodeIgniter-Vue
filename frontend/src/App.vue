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

    // limpiar formulario
    nuevoProducto.value = {
      nombre: "",
      precio_actual: 0,
      precio_objetivo: 0,
    };

    // recargar lista
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

    // reset
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

onMounted(() => {
  obtenerProductos();
});
</script>

<template>
  <div>
    <h1>Lista de Productos</h1>
    <form @submit.prevent="editando ? actualizarProducto() : crearProducto()">
      <input v-model="nuevoProducto.nombre" placeholder="Nombre" required />

      <input
        v-model.number="nuevoProducto.precio_actual"
        type="number"
        placeholder="Precio actual"
        required
      />

      <input
        v-model.number="nuevoProducto.precio_objetivo"
        type="number"
        placeholder="Precio objetivo"
        required
      />

      <button type="submit">
        {{ editando ? "Actualizar producto" : "Crear producto" }}
      </button>
    </form>
    <ul>
      <li v-for="producto in productos" :key="producto.id">
        {{ producto.nombre }} - ${{ producto.precio_actual }}
        <button @click="cargarProducto(producto)">Editar</button>
        <button @click="eliminarProducto(producto.id)">Eliminar</button>
      </li>
    </ul>
  </div>
</template>
