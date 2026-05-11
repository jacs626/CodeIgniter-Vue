import { defineStore } from 'pinia'
import api from '../composables/api'

export interface User {
  id: number
  nombre: string
  email: string
  created_at?: string
  updated_at?: string
}

interface AuthState {
  user: User | null
  token: string | null
  isAuthenticated: boolean
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: localStorage.getItem('token'),
    isAuthenticated: false
  }),

  actions: {
    async login(email: string, password: string) {
      const response = await api.post('/auth/login', {
        email,
        password
      })

      this.user = response.data.user
      this.token = response.data.token
      this.isAuthenticated = true
      if (this.token) {
        localStorage.setItem('token', this.token)
      }

      return response
    },

    async register(nombre: string, email: string, password: string) {
      const response = await api.post('/auth/register', {
        nombre,
        email,
        password
      })

      this.user = response.data.user
      this.token = response.data.token
      this.isAuthenticated = true
      if (this.token) {
        localStorage.setItem('token', this.token)
      }

      return response
    },

    async restoreSession() {
      const token = localStorage.getItem('token')
      if (!token) {
        return
      }

      try {
        const response = await api.get('/auth/me')
        this.user = response.data
        this.token = token
        this.isAuthenticated = true
      } catch (error) {
        this.logout()
      }
    },

    logout() {
      this.user = null
      this.token = null
      this.isAuthenticated = false
      localStorage.removeItem('token')
    },

    getToken(): string | null {
      return this.token
    }
  }
})