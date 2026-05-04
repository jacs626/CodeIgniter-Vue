<script setup lang="ts">
import { ref } from 'vue';
import { useProducto } from './composables/useProducto';
import ProductForm from './components/ProductForm/index.vue';
import ProductList from './components/ProductList/index.vue';
import './styles/global.css';

const { productos, obtenerProductos, crearProducto, actualizarProducto, eliminarProducto } = useProducto();

const nuevoProducto = ref({
  nombre: '',
  precio_actual: 0,
  precio_objetivo: 0,
});

const editando = ref(false);
const productoEditandoId = ref<number | null>(null);

const cargarProducto = (producto: typeof nuevoProducto.value & { id: number }) => {
  nuevoProducto.value = { ...producto };
  productoEditandoId.value = producto.id;
  editando.value = true;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const handleSubmit = () => {
  if (editando.value && productoEditandoId.value) {
    actualizarProducto(productoEditandoId.value, nuevoProducto.value);
    resetForm();
  } else {
    crearProducto(nuevoProducto.value);
    resetForm();
  }
};

const resetForm = () => {
  editando.value = false;
  productoEditandoId.value = null;
  nuevoProducto.value = {
    nombre: '',
    precio_actual: 0,
    precio_objetivo: 0,
  };
};

obtenerProductos();
</script>

<template>
  <div class="container">
    <header class="header">
      <h1>Gestión de Precios</h1>
    </header>

    <main class="main-content">
      <ProductForm
        v-model="nuevoProducto"
        :editando="editando"
        @submit="handleSubmit"
        @cancelar="resetForm"
      />

      <ProductList
        :productos="productos"
        @editar="cargarProducto"
        @eliminar="eliminarProducto"
      />
    </main>
  </div>
</template>