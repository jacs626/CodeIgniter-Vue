<script setup lang="ts">
import { useAlertas } from "../../composables/useAlertas";
import "./index.css";

const { alertas, cargando, error } = useAlertas();
</script>

<template>
  <div class="alertas-panel">
    <div class="alertas-header">
      <h3>Alertas de Precio 🚨</h3>
      <span class="alerta-contador" v-if="alertas.length > 0">{{ alertas.length }}</span>
    </div>

    <div v-if="cargando && alertas.length === 0" class="alertas-loading">Cargando alertas...</div>

    <div v-else-if="error" class="alertas-error">
      {{ error }}
    </div>

    <div v-else-if="alertas.length === 0" class="alertas-empty">Sin alertas ✅</div>

    <ul v-else class="alertas-lista">
      <li v-for="producto in alertas" :key="producto.id" class="alerta-item">
        <span class="alerta-nombre">{{ producto.nombre }}</span>
        <span class="alerta-precio">\${{ Number(producto.precio_actual).toFixed(2) }}</span>
      </li>
    </ul>
  </div>
</template>
