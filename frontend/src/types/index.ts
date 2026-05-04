export interface Producto {
  id: number;
  nombre: string;
  precio_actual: string | number;
  precio_objetivo: string | number;
  en_oferta?: boolean;
}

export interface ProductoForm {
  nombre: string;
  precio_actual: number;
  precio_objetivo: number;
}