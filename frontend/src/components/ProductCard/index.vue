<script setup lang="ts">
import type { Producto } from '../../types';
import './index.css';

defineProps<{
  producto: Producto;
}>();

defineEmits<{
  (e: 'editar', producto: Producto): void;
  (e: 'eliminar', id: number): void;
}>();
</script>

<template>
  <article class="product-card">
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
      <button class="btn btn-edit" @click="$emit('editar', producto)">Editar</button>
      <button class="btn btn-delete" @click="$emit('eliminar', producto.id)">Eliminar</button>
    </div>
  </article>
</template>