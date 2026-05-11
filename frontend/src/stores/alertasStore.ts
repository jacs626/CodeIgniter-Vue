import { defineStore } from 'pinia'
import api from '../composables/api'
import type { Producto } from '../types'

export const useAlertasStore = defineStore('alertas', {
  state: () => ({
    alertas: [] as Producto[],
    productosVistos: new Set<number>(),
    cargando: false,
    error: null as string | null,
    lastAlertaTime: 0
  }),

  actions: {
    async obtenerAlertas() {
      try {
        this.cargando = true
        this.error = null

        const response = await api.get("/productos/alertas") as unknown as { status: string; data: Producto[] }

        if (response.status === "success") {
          const productos = response.data

          const nuevas = productos.filter((p) => {
            return !this.productosVistos.has(p.id)
          })

          if (nuevas.length > 0) {
            nuevas.forEach((p) => {
              this.productosVistos.add(p.id)
            })
            this.reproducirSonido()
          }

          this.alertas = productos
        }
      } catch (e: any) {
        this.error = "Error de conexión"
      } finally {
        this.cargando = false
      }
    },

    reproducirSonido() {
      const now = Date.now()
      if (now - this.lastAlertaTime < 1000) return
      this.lastAlertaTime = now

      try {
        const audioContext = new (window.AudioContext || (window as any).webkitAudioContext)()
        const oscillator = audioContext.createOscillator()
        const gainNode = audioContext.createGain()

        oscillator.connect(gainNode)
        gainNode.connect(audioContext.destination)

        oscillator.frequency.value = 800
        oscillator.type = "sine"
        gainNode.gain.value = 0.3

        oscillator.start()
        setTimeout(() => oscillator.stop(), 200)
      } catch (e) {
        console.error("Error al reproducir sonido:", e)
      }
    },

    limpiarVistos() {
      this.productosVistos = new Set()
    }
  }
})