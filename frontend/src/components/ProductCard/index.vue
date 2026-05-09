<script setup lang="ts">
import type { Producto } from "../../types";
import "./index.css";

defineProps<{
  producto: Producto;
}>();

defineEmits<{
  (e: "editar", producto: Producto): void;
  (e: "eliminar", id: number): void;
}>();
</script>

<template>
  <article class="product-card" :class="{ 'en-oferta': producto.en_oferta }">
    <div class="product-info">
      <div class="product-header">
        <h3>{{ producto.nombre }}</h3>
        <span v-if="producto.en_oferta" class="badge-oferta">🔥</span>
      </div>
      <div class="prices">
        <div class="price-item">
          <span class="label">Actual</span>
          <span class="value">${{ Number(producto.precio_actual || 0).toFixed(2) }}</span>
        </div>
        <div class="price-item">
          <span class="label">Objetivo</span>
          <span class="value">${{ Number(producto.precio_objetivo || 0).toFixed(2) }}</span>
        </div>
      </div>
    </div>
    <div class="product-actions">
      <button class="btn btn-edit" @click="$emit('editar', producto)">Editar</button>
      <button class="btn btn-delete" @click="$emit('eliminar', producto.id)">Eliminar</button>
    </div>
  </article>
</template>
