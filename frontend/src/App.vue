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

onMounted(() => {
  obtenerProductos();
});
</script>

<template>
  <div>
    <h1>Lista de Productos</h1>
    <form @submit.prevent="crearProducto">
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

      <button type="submit">Crear producto</button>
    </form>
    <ul>
      <li v-for="producto in productos" :key="producto.id">
        {{ producto.nombre }} - ${{ producto.precio_actual }}
      </li>
    </ul>
  </div>
</template>
