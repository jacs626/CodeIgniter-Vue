<script setup lang="ts">
import type { Producto } from '../../types';
import ProductCard from '../ProductCard/index.vue';
import './index.css';

defineProps<{
  productos: Producto[];
  searchQuery: string;
  onlyOffers?: boolean;
  currentPage?: number;
  totalPages?: number;
}>();

const emit = defineEmits<{
  (e: 'editar', producto: Producto): void;
  (e: 'eliminar', id: number): void;
  (e: 'update:searchQuery', value: string): void;
  (e: 'update:onlyOffers', value: boolean): void;
  (e: 'changePage', page: number): void;
}>();
</script>

<template>
  <section class="list-section">
    <div class="filters">
      <input 
        type="text" 
        :value="searchQuery"
        @input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
        placeholder="Buscar por nombre..."
        class="search-input"
      />
      <label class="filter-offer">
        <input 
          type="checkbox" 
          :checked="onlyOffers"
          @change="emit('update:onlyOffers', ($event.target as HTMLInputElement).checked)"
        />
        <span>Solo ofertas 🔥</span>
      </label>
    </div>
    <h2>Productos ({{ productos.length }})</h2>
    <div v-if="productos.length === 0" class="empty-state">No hay productos registrados</div>
    <div v-else class="product-grid">
      <ProductCard 
        v-for="producto in productos" 
        :key="producto.id" 
        :producto="producto" 
        @editar="emit('editar', $event)" 
        @eliminar="emit('eliminar', $event)" 
      />
    </div>
    <div v-if="totalPages && totalPages > 1" class="pagination">
      <button 
        :disabled="currentPage === 1" 
        @click="emit('changePage', currentPage! - 1)"
      >
        Anterior
      </button>
      <span>Página {{ currentPage }} de {{ totalPages }}</span>
      <button 
        :disabled="currentPage === totalPages" 
        @click="emit('changePage', currentPage! + 1)"
      >
        Siguiente
      </button>
    </div>
  </section>
</template>