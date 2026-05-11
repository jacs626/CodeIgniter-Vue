<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useProducto } from '../../../composables/useProducto';
import { useAlertasStore } from '../../../stores/alertasStore';
import ProductForm from '../ProductForm/index.vue';
import ProductList from '../ProductList/index.vue';
import AlertasPrecio from '../../alertas/AlertasPrecio/index.vue';
import type { Producto, ProductoForm } from '../../../types';

const { productos, error, searchQuery, onlyOffers, currentPage, totalPages, cambiarPagina, obtenerProductos, crearProducto, actualizarProducto, eliminarProducto } = useProducto();
const alertasStore = useAlertasStore();

const editando = ref(false);
const productoEditando = ref<Producto | null>(null);
const formData = ref<ProductoForm>({
  nombre: '',
  precio_actual: 0,
  precio_objetivo: 0,
});

const handleEditar = (producto: Producto) => {
  editando.value = true;
  productoEditando.value = producto;
  formData.value = {
    nombre: producto.nombre,
    precio_actual: Number(producto.precio_actual),
    precio_objetivo: Number(producto.precio_objetivo),
  };
};

const handleSubmit = async () => {
  if (editando.value && productoEditando.value) {
    await actualizarProducto(productoEditando.value.id, formData.value);
  } else {
    await crearProducto(formData.value);
  }
  resetForm();
  alertasStore.limpiarVistos();
  await alertasStore.obtenerAlertas();
};

const resetForm = () => {
  editando.value = false;
  productoEditando.value = null;
  formData.value = {
    nombre: '',
    precio_actual: 0,
    precio_objetivo: 0,
  };
};

onMounted(() => {
  obtenerProductos();
});
</script>

<template>
  <div class="products-page">
    <AlertasPrecio />

    <ProductForm
      v-model="formData"
      :editando="editando"
      :error="error"
      @submit="handleSubmit"
      @cancelar="resetForm"
    />

    <ProductList
      :productos="productos"
      :searchQuery="searchQuery"
      :onlyOffers="onlyOffers"
      :currentPage="currentPage"
      :totalPages="totalPages"
      @editar="handleEditar"
      @eliminar="eliminarProducto"
      @update:searchQuery="searchQuery = $event"
      @update:onlyOffers="onlyOffers = $event"
      @changePage="cambiarPagina"
    />
  </div>
</template>

<style scoped>
.products-page {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}
</style>