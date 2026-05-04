import { ref } from 'vue';
import type { Producto, ProductoForm } from '../types';

const API_URL = 'http://localhost:8080/productos';

export function useProducto() {
  const productos = ref<Producto[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const obtenerProductos = async () => {
    loading.value = true;
    error.value = null;
    try {
      const res = await fetch(API_URL);
      const json = await res.json();
      if (json.status === 'success') {
        productos.value = json.data;
      } else {
        error.value = json.message;
      }
    } catch (e) {
      error.value = 'Error al obtener productos';
      console.error(e);
    } finally {
      loading.value = false;
    }
  };

  const crearProducto = async (data: ProductoForm) => {
    loading.value = true;
    error.value = null;
    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });
      const json = await res.json();
      if (json.status !== 'success') {
        error.value = json.message;
        return;
      }
      await obtenerProductos();
    } catch (e) {
      error.value = 'Error al crear producto';
      console.error(e);
    } finally {
      loading.value = false;
    }
  };

  const actualizarProducto = async (id: number, data: ProductoForm) => {
    loading.value = true;
    error.value = null;
    try {
      const res = await fetch(`${API_URL}/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });
      const json = await res.json();
      if (json.status !== 'success') {
        error.value = json.message;
        return;
      }
      await obtenerProductos();
    } catch (e) {
      error.value = 'Error al actualizar producto';
      console.error(e);
    } finally {
      loading.value = false;
    }
  };

  const eliminarProducto = async (id: number) => {
    if (!confirm('¿Estás seguro de eliminar este producto?')) return;
    
    loading.value = true;
    error.value = null;
    try {
      const res = await fetch(`${API_URL}/${id}`, { method: 'DELETE' });
      const json = await res.json();
      if (json.status !== 'success') {
        error.value = json.message;
        return;
      }
      await obtenerProductos();
    } catch (e) {
      error.value = 'Error al eliminar producto';
      console.error(e);
    } finally {
      loading.value = false;
    }
  };

  return {
    productos,
    loading,
    error,
    obtenerProductos,
    crearProducto,
    actualizarProducto,
    eliminarProducto,
  };
}