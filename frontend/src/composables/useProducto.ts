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

api.interceptors.request.use(
  (config) => {
    config.headers.Authorization = 'Bearer mysecrettoken123';
    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

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
  const onlyOffers = ref(false);
  const currentPage = ref(1);
  const totalPages = ref(1);

  watch(searchQuery, () => {
    currentPage.value = 1;
    if (!searchQuery.value.trim()) {
      obtenerProductos();
      return;
    }
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      obtenerProductos();
    }, 300);
  });

  watch(() => onlyOffers.value, () => {
    currentPage.value = 1;
    obtenerProductos();
  });

  const obtenerProductos = async () => {
    loading.value = true;
    error.value = null;
    try {
      const res = await api.get('/productos', {
        params: {
          q: searchQuery.value || undefined,
          page: currentPage.value,
          soloOfertas: onlyOffers.value ? 1 : undefined
        }
      }) as ApiResponse<Producto[]> & { meta?: { currentPage: number; pageCount: number } };
      
      if (res.status === 'success') {
        productos.value = [...res.data];
        if (res.meta) {
          currentPage.value = res.meta.currentPage;
          totalPages.value = res.meta.pageCount;
          if (currentPage.value > totalPages.value) {
            currentPage.value = 1;
            obtenerProductos();
            return;
          }
        }
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
      const res = await api.post('/productos', data) as ApiResponse<void> & { messages?: Record<string, string> };
      if (res.status !== 'success') {
        error.value = res.messages 
          ? Object.values(res.messages).join(', ') 
          : res.message;
        return;
      }
      await obtenerProductos();
    } catch (e: unknown) {
      const err = e as { messages?: Record<string, string>; message?: string };
      error.value = err.messages 
        ? Object.values(err.messages).join(', ') 
        : err.message || 'Error al crear producto';
    } finally {
      loading.value = false;
    }
  };

  const actualizarProducto = async (id: number, data: ProductoForm) => {
    loading.value = true;
    error.value = null;
    try {
      const res = await api.put(`/productos/${id}`, data) as ApiResponse<void> & { messages?: Record<string, string> };
      if (res.status !== 'success') {
        error.value = res.messages 
          ? Object.values(res.messages).join(', ') 
          : res.message;
        return;
      }
      await obtenerProductos();
    } catch (e: unknown) {
      const err = e as { messages?: Record<string, string>; message?: string };
      error.value = err.messages 
        ? Object.values(err.messages).join(', ') 
        : err.message || 'Error al actualizar producto';
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

  const cambiarPagina = (page: number) => {
    currentPage.value = page;
    obtenerProductos();
  };

  return {
    productos,
    loading,
    error,
    searchQuery,
    onlyOffers,
    currentPage,
    totalPages,
    cambiarPagina,
    obtenerProductos,
    crearProducto,
    actualizarProducto,
    eliminarProducto,
  };
}