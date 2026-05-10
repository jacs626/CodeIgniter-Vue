import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import type { Producto } from "../types";

const api = axios.create({
  baseURL: "http://localhost:8080",
  headers: {
    "Content-Type": "application/json",
  },
});

const productosVistos = ref<Set<number>>(new Set());

export function useAlertas() {
  const alertas = ref<Producto[]>([]);
  const cargando = ref(false);
  const error = ref<string | null>(null);

  let intervalId: ReturnType<typeof setInterval> | null = null;

  const reproducirSonido = () => {
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

      if (result.status === "success") {
        const productos = result.data as Producto[];
        const productosEnOfertaIds = new Set(productos.map(p => p.id));

        const alertasActuales = alertas.value.filter(a => 
          productosEnOfertaIds.has(a.id)
        );

        const nuevas = productos.filter((p) => {
          return !productosVistos.value.has(p.id);
        });

        if (nuevas.length > 0) {
          nuevas.forEach((p) => {
            productosVistos.value.add(p.id);
          });
          reproducirSonido();
          console.log("Nuevas alertas:", nuevas);
        }

        alertas.value = [...nuevas, ...alertasActuales];
      }
    } catch (e: any) {
      error.value = "Error de conexión";
      console.error("Error fetching alertas:", e);
    } finally {
      cargando.value = false;
    }
  };

  const iniciarPolling = () => {
    obtenerAlertas();

    intervalId = setInterval(() => {
      obtenerAlertas();
    }, 5000);
  };

  const detenerPolling = () => {
    if (intervalId) {
      clearInterval(intervalId);
      intervalId = null;
    }
  };

  onMounted(() => {
    iniciarPolling();
  });

  onUnmounted(() => {
    detenerPolling();
  });

  return {
    alertas,
    cargando,
    error,
    obtenerAlertas,
    iniciarPolling,
    detenerPolling,
  };
}
