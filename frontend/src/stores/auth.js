import { defineStore } from 'pinia'
import apiClient from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') || null,
    user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    async login(email, password) {
      this.loading = true
      this.error = null
      try {
        const response = await apiClient.post('/login', { email, password })
        this.token = response.data.access_token
        this.user = response.data.user
        
        // Simpan token dan data user ke localStorage (Soal 2a)
        localStorage.setItem('auth_token', this.token)
        localStorage.setItem('auth_user', JSON.stringify(this.user))
        
        return { success: true }
      } catch (err) {
        this.error = err.response?.data?.message || 'Login gagal, periksa kredensial Anda.'
        return { 
          success: false, 
          message: this.error,
          errors: err.response?.data?.errors || {}
        }
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        if (this.token) {
          await apiClient.post('/logout')
        }
      } catch (err) {
        console.error('Logout error:', err)
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      }
    },

    async fetchUser() {
      if (!this.token) return
      try {
        const response = await apiClient.get('/user')
        this.user = response.data.data
        localStorage.setItem('auth_user', JSON.stringify(this.user))
      } catch (err) {
        this.logout()
      }
    }
  },
})
