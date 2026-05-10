import { ref, computed } from 'vue';
import api from './api';
import type { ProductoDetalle } from '../types';

interface ApiResponse<T> {
  status: string;
  data: T;
  message: string;
}

export function useProductoDetalle() {
  const producto = ref<ProductoDetalle | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const notFound = ref(false);

  const enOferta = computed(() => {
    if (!producto.value) return false;
    return producto.value.en_oferta ?? 
      Number(producto.value.precio_actual) <= Number(producto.value.precio_objetivo);
  });

  const obtenerProducto = async (id: number) => {
    loading.value = true;
    error.value = null;
    notFound.value = false;
    producto.value = null;

    try {
      const res = await api.get(`/productos/${id}`) as ApiResponse<ProductoDetalle>;

      if (res.status === 'success') {
        producto.value = res.data;
      } else {
        error.value = res.message;
      }
    } catch (e: unknown) {
      const err = e as { status?: number; message?: string };
      if (err.status === 404) {
        notFound.value = true;
        error.value = 'Producto no encontrado';
      } else {
        error.value = err.message || 'Error al cargar el producto';
      }
    } finally {
      loading.value = false;
    }
  };

  return {
    producto,
    loading,
    error,
    notFound,
    enOferta,
    obtenerProducto,
  };
}