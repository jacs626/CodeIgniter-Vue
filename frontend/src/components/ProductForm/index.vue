<script setup lang="ts">
import { computed } from 'vue';
import type { ProductoForm } from '../../types';
import './index.css';

interface Props {
  modelValue: ProductoForm;
  editando: boolean;
  error?: string | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: ProductoForm): void;
  (e: 'submit'): void;
  (e: 'cancelar'): void;
}>();

const formData = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});
</script>

<template>
  <section class="form-section">
    <h2>{{ editando ? 'Editar Producto' : 'Nuevo Producto' }}</h2>
    <form @submit.prevent="emit('submit')">
      <div v-if="error" class="error-message">{{ error }}</div>
      <div class="form-group">
        <label for="nombre">Nombre</label>
        <input 
          id="nombre" 
          v-model="formData.nombre" 
          placeholder="Ingresa el nombre del producto" 
          required 
          minlength="3"
          maxlength="100"
        />
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="precio_actual">Precio Actual</label>
          <input 
            id="precio_actual" 
            v-model.number="formData.precio_actual" 
            type="number" 
            step="1" 
            min="1"
            placeholder="0.00" 
            required 
          />
        </div>
        <div class="form-group">
          <label for="precio_objetivo">Precio Objetivo</label>
          <input 
            id="precio_objetivo" 
            v-model.number="formData.precio_objetivo" 
            type="number" 
            step="1" 
            min="1"
            placeholder="0.00" 
            required 
          />
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">{{ editando ? 'Actualizar' : 'Crear' }}</button>
        <button v-if="editando" type="button" class="btn btn-secondary" @click="emit('cancelar')">Cancelar</button>
      </div>
    </form>
  </section>
</template>