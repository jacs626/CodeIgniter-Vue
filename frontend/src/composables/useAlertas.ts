import { ref, onMounted, onUnmounted } from "vue";
import api from "./api";
import type { Producto } from "../types";

const alertasGlobales = ref<Producto[]>([]);
const productosVistos = ref<Set<number>>(new Set());
let intervalId: ReturnType<typeof setInterval> | null = null;
let lastAlertaTime = 0;

export function useAlertas() {
  const cargando = ref(false);
  const error = ref<string | null>(null);

  const reproducirSonido = () => {
    const now = Date.now();
    if (now - lastAlertaTime < 1000) return;
    lastAlertaTime = now;

    try {
      const audioContext = new (window.AudioContext || (window as any).webkitAudioContext)();
      const oscillator = audioContext.createOscillator();
      const gainNode = audioContext.createGain();

      oscillator.connect(gainNode);
      gainNode.connect(audioContext.destination);

      oscillator.frequency.value = 800;
      oscillator.type = "sine";
      gainNode.gain.value = 0.3;

      oscillator.start();
      setTimeout(() => oscillator.stop(), 200);
    } catch (e) {
      console.error("Error al reproducir sonido:", e);
    }
  };

  const obtenerAlertas = async () => {
    try {
      cargando.value = true;
      error.value = null;

      const response = await api.get("/productos/alertas");
      const result = response.data;

      console.log('obtenerAlertas - result:', result);
      console.log('obtenerAlertas - alertasGlobales antes:', alertasGlobales.value.length);

      if (result.status === "success") {
        const productos = result.data as Producto[];
        console.log('obtenerAlertas - productos recibidos:', productos.length);

        const nuevas = productos.filter((p) => {
          return !productosVistos.value.has(p.id);
        });
        console.log('obtenerAlertas - nuevas:', nuevas.length);

        if (nuevas.length > 0) {
          nuevas.forEach((p) => {
            productosVistos.value.add(p.id);
          });
          reproducirSonido();
        }

        alertasGlobales.value = productos;
        console.log('obtenerAlertas - alertasGlobales después:', alertasGlobales.value.length);
      }
    } catch (e: any) {
      error.value = "Error de conexión";
      console.error("Error fetching alertas:", e);
    } finally {
      cargando.value = false;
    }
  };

  const iniciarPolling = () => {
    if (intervalId) return;
    
    obtenerAlertas();

    intervalId = setInterval(() => {
      obtenerAlertas();
    }, 3000);
  };

  const detenerPolling = () => {
    if (intervalId) {
      clearInterval(intervalId);
      intervalId = null;
    }
  };

  const limpiarVistos = () => {
    productosVistos.value = new Set();
  };

  onMounted(() => {
    iniciarPolling();
  });

  onUnmounted(() => {
  });

  return {
    alertas: alertasGlobales,
    cargando,
    error,
    obtenerAlertas,
    iniciarPolling,
    detenerPolling,
    limpiarVistos,
  };
}
