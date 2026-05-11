import { defineStore } from 'pinia'
import api from '../composables/api'

export interface User {
  id: number
  nombre: string
  email: string
  created_at?: string
  updated_at?: string
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as User | null,
    token: null as string | null,
    isAuthenticated: false,
    initialized: false
  }),

  actions: {
    initialize() {
      const token = localStorage.getItem('token')
      if (token) {
        this.token = token
        this.isAuthenticated = true
        this.fetchUser()
      }
      this.initialized = true
    },

    async fetchUser() {
      try {
        const response = await api.get('/auth/me')
        this.user = response.data
      } catch (e) {
        this.logout()
      }
    },

    setToken(token: string) {
      this.token = token
      this.isAuthenticated = true
      localStorage.setItem('token', token)
    },

    async login(email: string, password: string) {
      const response = await api.post('/auth/login', { email, password })
      
      this.setToken(response.data.token)
      this.user = response.data.user

      return response
    },

    async register(nombre: string, email: string, password: string) {
      const response = await api.post('/auth/register', { nombre, email, password })
      
      this.setToken(response.data.token)
      this.user = response.data.user

      return response
    },

    logout() {
      this.user = null
      this.token = null
      this.isAuthenticated = false
      localStorage.removeItem('token')
    }
  }
})