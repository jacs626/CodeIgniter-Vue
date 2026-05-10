<script setup lang="ts">
import { ref } from "vue";
import { useAlertas } from "../../composables/useAlertas";
import "./index.css";

const { alertas, cargando, error } = useAlertas();
const expandido = ref(false);

const toggle = () => {
  expandido.value = !expandido.value;
};
</script>

<template>
  <div class="alertas-panel">
    <div class="alertas-header" @click="toggle" style="cursor: pointer;">
      <h3>Alertas de Precio 🚨</h3>
      <span class="alerta-contador" v-if="alertas.length > 0">{{ alertas.length }}</span>
      <span class="alertas-toggle">{{ expandido.value ? '▲' : '▼' }}</span>
    </div>

    <div v-show="expandido">
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
  </div>
</template>
