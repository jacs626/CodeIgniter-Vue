export interface Producto {
  id: number;
  nombre: string;
  precio_actual: number;
  precio_objetivo: number;
}

export interface ProductoForm {
  nombre: string;
  precio_actual: number;
  precio_objetivo: number;
}