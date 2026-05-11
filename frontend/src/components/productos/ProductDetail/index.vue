<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useProductoDetalle } from '../../../composables/useProductoDetalle';

const props = defineProps<{
  id: string;
}>();

const route = useRoute();
const router = useRouter();
const { producto, loading, error, notFound, enOferta, obtenerProducto } = useProductoDetalle();

const formatPrice = (value: string | number | undefined | null) => {
  return Number(value || 0).toFixed(2);
};

const formatDate = (date: string | null | undefined) => {
  if (!date) return '-';
  const parsed = new Date(date);
  if (isNaN(parsed.getTime())) return '-';
  return parsed.toLocaleString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const volver = () => {
  router.push('/productos');
};

const loadProducto = (id: string) => {
  const numericId = parseInt(id, 10);
  if (!isNaN(numericId)) {
    obtenerProducto(numericId);
  }
};

onMounted(() => {
  loadProducto(props.id);
});

watch(() => props.id, (newId) => {
  loadProducto(newId);
});
</script>

<template>
  <div class="detalle-container">
    <button class="btn-volver" @click="volver">
      <span class="arrow">←</span> Volver a productos
    </button>

    <div v-if="loading" class="loading-container">
      <div class="skeleton-card">
        <div class="skeleton-header"></div>
        <div class="skeleton-price"></div>
        <div class="skeleton-badge"></div>
        <div class="skeleton-dates"></div>
      </div>
    </div>

    <div v-else-if="notFound" class="error-container">
      <div class="error-icon">🔍</div>
      <h2>Producto no encontrado</h2>
      <p>El producto que buscas no existe o fue eliminado.</p>
      <button class="btn btn-primary" @click="volver">Ver todos los productos</button>
    </div>

    <div v-else-if="error && !notFound" class="error-container">
      <div class="error-icon">⚠️</div>
      <h2>Error al cargar</h2>
      <p>{{ error }}</p>
      <button class="btn btn-primary" @click="loadProducto(id)">Reintentar</button>
    </div>

    <Transition name="fade" mode="out-in">
      <article v-if="producto" class="producto-detalle">
        <header class="detalle-header">
          <h1>{{ producto.nombre }}</h1>
          <span v-if="enOferta" class="badge-oferta">En oferta</span>
        </header>

        <div class="precios-section">
          <div class="precio-card precio-actual">
            <span class="label">Precio Actual</span>
            <span class="value">${{ formatPrice(producto.precio_actual) }}</span>
          </div>
          <div class="precio-card precio-objetivo">
            <span class="label">Precio Objetivo</span>
            <span class="value">${{ formatPrice(producto.precio_objetivo) }}</span>
          </div>
        </div>

        <div class="estado-section">
          <div class="estado-item">
            <span class="estado-label">Estado</span>
            <span :class="['estado-badge', enOferta ? 'en-oferta' : 'sin-oferta']">
              {{ enOferta ? 'En oferta' : 'Sin oferta' }}
            </span>
          </div>
        </div>

        <footer class="fechas-section">
          <div class="fecha-item">
            <span class="fecha-label">Creado</span>
            <span class="fecha-value">{{ formatDate(producto.created_at) }}</span>
          </div>
          <div class="fecha-item">
            <span class="fecha-label">Última actualización</span>
            <span class="fecha-value">{{ formatDate(producto.updated_at) }}</span>
          </div>
        </footer>
      </article>
    </Transition>
  </div>
</template>

<style scoped>
.detalle-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.btn-volver {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: transparent;
  border: 1px solid #ddd;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.9rem;
  color: #666;
  transition: all 0.2s ease;
  margin-bottom: 1.5rem;
}

.btn-volver:hover {
  background: #f5f5f5;
  border-color: #ccc;
  color: #333;
}

.arrow {
  font-size: 1.2rem;
}

.loading-container {
  display: flex;
  justify-content: center;
  padding: 2rem;
}

.skeleton-card {
  background: #fff;
  border-radius: 12px;
  padding: 2rem;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.skeleton-header {
  height: 40px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  width: 70%;
}

.skeleton-price {
  height: 60px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 8px;
  margin-bottom: 1rem;
  width: 50%;
}

.skeleton-badge {
  height: 30px;
  width: 100px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 20px;
  margin-bottom: 1.5rem;
}

.skeleton-dates {
  height: 40px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 8px;
}

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.error-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.error-container h2 {
  color: #333;
  margin: 0 0 0.5rem;
}

.error-container p {
  color: #666;
  margin: 0 0 1.5rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover {
  background: #0056b3;
}

.producto-detalle {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.detalle-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
}

.detalle-header h1 {
  margin: 0;
  font-size: 1.8rem;
  color: #333;
}

.badge-oferta {
  background: #28a745;
  color: white;
  padding: 0.4rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
}

.precios-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.precio-card {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
}

.precio-card .label {
  display: block;
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 0.5rem;
}

.precio-card .value {
  font-size: 2rem;
  font-weight: 700;
  color: #333;
}

.precio-actual .value {
  color: #28a745;
}

.precio-objetivo .value {
  color: #666;
}

.estado-section {
  margin-bottom: 1.5rem;
}

.estado-item {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.estado-label {
  font-size: 0.9rem;
  color: #666;
}

.estado-badge {
  padding: 0.4rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
}

.estado-badge.en-oferta {
  background: #d4edda;
  color: #155724;
}

.estado-badge.sin-oferta {
  background: #f8d7da;
  color: #721c24;
}

.fechas-section {
  display: flex;
  flex-wrap: wrap;
  gap: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #eee;
}

.fecha-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.fecha-label {
  font-size: 0.8rem;
  color: #999;
}

.fecha-value {
  font-size: 0.9rem;
  color: #666;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 600px) {
  .detalle-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .precios-section {
    grid-template-columns: 1fr;
  }

  .fechas-section {
    flex-direction: column;
    gap: 1rem;
  }
}
</style>