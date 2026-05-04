<script setup lang="ts">
import type { Producto } from '../../types';
import ProductCard from '../ProductCard/index.vue';
import './index.css';

defineProps<{
  productos: Producto[];
}>();

defineEmits<{
  (e: 'editar', producto: Producto): void;
  (e: 'eliminar', id: number): void;
}>();
</script>

<template>
  <section class="list-section">
    <h2>Productos ({{ productos.length }})</h2>
    <div v-if="productos.length === 0" class="empty-state">No hay productos registrados</div>
    <div v-else class="product-grid">
      <ProductCard v-for="producto in productos" :key="producto.id" :producto="producto" @editar="$emit('editar', $event)" @eliminar="$emit('eliminar', $event)" />
    </div>
  </section>
</template>