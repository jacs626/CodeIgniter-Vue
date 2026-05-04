<script setup lang="ts">
import { computed } from 'vue';
import type { Producto } from '../../types';
import ProductCard from '../ProductCard/index.vue';
import './index.css';

const props = defineProps<{
  productos: Producto[];
  searchQuery: string;
  onlyOffers?: boolean;
}>();

const emit = defineEmits<{
  (e: 'editar', producto: Producto): void;
  (e: 'eliminar', id: number): void;
  (e: 'update:searchQuery', value: string): void;
  (e: 'update:onlyOffers', value: boolean): void;
}>();

const filteredProducts = computed(() => {
  let result = props.productos;
  
  if (props.onlyOffers) {
    result = result.filter(p => p.en_oferta);
  }
  
  return result;
});
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
    <h2>Productos ({{ filteredProducts.length }})</h2>
    <div v-if="filteredProducts.length === 0" class="empty-state">No hay productos registrados</div>
    <div v-else class="product-grid">
      <ProductCard 
        v-for="producto in filteredProducts" 
        :key="producto.id" 
        :producto="producto" 
        @editar="emit('editar', $event)" 
        @eliminar="emit('eliminar', $event)" 
      />
    </div>
  </section>
</template>