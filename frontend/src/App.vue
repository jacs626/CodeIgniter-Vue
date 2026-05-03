<script setup lang="ts">
import { ref, onMounted } from "vue";

interface Producto {
  id: number;
  nombre: string;
  precio_actual: number;
  precio_objetivo: number;
}

const productos = ref<Producto[]>([]);

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

    <ul>
      <li v-for="producto in productos" :key="producto.id">
        {{ producto.nombre }} - ${{ producto.precio_actual }}
      </li>
    </ul>
  </div>
</template>
