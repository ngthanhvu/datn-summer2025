import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('authStore', {
    state: () => ({
        user: null,
        isLoadingUser: false,
        error: null
    }),
    actions: {
        async fetchUser() {
            this.isLoadingUser = true
            this.error = null
            try {
                const res = await axios.get('/api/user')
                this.user = res.data
            } catch (err) {
                this.error = err
            } finally {
                this.isLoadingUser = false
            }
        },
        async login(credentials) {
            this.isLoadingUser = true
            this.error = null
            try {
                const res = await axios.post('/api/login', credentials)
                this.user = res.data
            } catch (err) {
                this.error = err
                throw err
            } finally {
                this.isLoadingUser = false
            }
        },
        async logout() {
            this.isLoadingUser = true
            this.error = null
            try {
                await axios.post('/api/logout')
                this.user = null
            } catch (err) {
                this.error = err
                throw err
            } finally {
                this.isLoadingUser = false
            }
        }
    }
}) 