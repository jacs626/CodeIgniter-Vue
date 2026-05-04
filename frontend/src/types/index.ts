export interface Producto {
  id: number;
  nombre: string;
  precio_actual: string | number;
  precio_objetivo: string | number;
}

export interface ProductoForm {
  nombre: string;
  precio_actual: number;
  precio_objetivo: number;
}