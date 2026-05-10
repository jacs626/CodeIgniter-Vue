<script setup lang="ts">
import { useRouter } from 'vue-router';
import type { Producto } from "../../types";
import "./index.css";

const props = defineProps<{
  producto: Producto;
}>();

defineEmits<{
  (e: "editar", producto: Producto): void;
  (e: "eliminar", id: number): void;
}>();

const router = useRouter();

const verDetalle = () => {
  router.push(`/productos/${props.producto.id}`);
};
</script>

<template>
  <article class="product-card" :class="{ 'en-oferta': props.producto.en_oferta }">
    <div class="product-info" @click="verDetalle">
      <div class="product-header">
        <h3>{{ props.producto.nombre }}</h3>
        <span v-if="props.producto.en_oferta" class="badge-oferta">🔥</span>
      </div>
      <div class="prices">
        <div class="price-item">
          <span class="label">Actual</span>
          <span class="value">${{ Number(props.producto.precio_actual || 0).toFixed(2) }}</span>
        </div>
        <div class="price-item">
          <span class="label">Objetivo</span>
          <span class="value">${{ Number(props.producto.precio_objetivo || 0).toFixed(2) }}</span>
        </div>
      </div>
    </div>
    <div class="product-actions">
      <button class="btn btn-view" @click="verDetalle">Ver</button>
      <button class="btn btn-edit" @click="$emit('editar', props.producto)">Editar</button>
      <button class="btn btn-delete" @click="$emit('eliminar', props.producto.id)">Eliminar</button>
    </div>
  </article>
</template>
