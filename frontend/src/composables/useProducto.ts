import { ref, watch } from 'vue';
import axios from 'axios';
import type { Producto, ProductoForm } from '../types';

interface ApiResponse<T> {
  status: string;
  data: T;
  message: string;
}

const api = axios.create({
  baseURL: 'http://localhost:8080',
  headers: {
    'Content-Type': 'application/json',
  },
});

api.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response) {
      return Promise.reject(error.response.data);
    }
    return Promise.reject({ message: 'Error de conexión' });
  }
);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

export function useProducto() {
  const productos = ref<Producto[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  const searchQuery = ref('');

  watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      obtenerProductos();
    }, 300);
  });

  const obtenerProductos = async () => {
    loading.value = true;
    error.value = null;
    try {
      const res = await api.get('/productos', {
        params: { q: searchQuery.value || undefined }
      }) as ApiResponse<Producto[]>;
      if (res.status === 'success') {
        productos.value = res.data;
      } else {
        error.value = res.message;
      }
    } catch (e: unknown) {
      const err = e as { message?: string };
      error.value = err.message || 'Error al obtener productos';
    } finally {
      loading.value = false;
    }
  };

  const crearProducto = async (data: ProductoForm) => {
    loading.value = true;
    error.value = null;
    try {
      const res = await api.post('/productos', data) as ApiResponse<void>;
      if (res.status !== 'success') {
        error.value = res.message;
        return;
      }
      await obtenerProductos();
    } catch (e: unknown) {
      const err = e as { message?: string };
      error.value = err.message || 'Error al crear producto';
    } finally {
      loading.value = false;
    }
  };

  const actualizarProducto = async (id: number, data: ProductoForm) => {
    loading.value = true;
    error.value = null;
    try {
      const res = await api.put(`/productos/${id}`, data) as ApiResponse<void>;
      if (res.status !== 'success') {
        error.value = res.message;
        return;
      }
      await obtenerProductos();
    } catch (e: unknown) {
      const err = e as { message?: string };
      error.value = err.message || 'Error al actualizar producto';
    } finally {
      loading.value = false;
    }
  };

  const eliminarProducto = async (id: number) => {
    if (!confirm('¿Estás seguro de eliminar este producto?')) return;
    
    loading.value = true;
    error.value = null;
    try {
      const res = await api.delete(`/productos/${id}`) as ApiResponse<void>;
      if (res.status !== 'success') {
        error.value = res.message;
        return;
      }
      await obtenerProductos();
    } catch (e: unknown) {
      const err = e as { message?: string };
      error.value = err.message || 'Error al eliminar producto';
    } finally {
      loading.value = false;
    }
  };

  return {
    productos,
    loading,
    error,
    searchQuery,
    obtenerProductos,
    crearProducto,
    actualizarProducto,
    eliminarProducto,
  };
}