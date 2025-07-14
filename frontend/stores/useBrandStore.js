import { defineStore } from 'pinia'
import axios from 'axios'

export const useBrandStore = defineStore('brandStore', {
    state: () => ({
        brands: [],
        isLoadingBrands: false,
        error: null
    }),
    actions: {
        async fetchBrands() {
            this.isLoadingBrands = true
            this.error = null
            try {
                const res = await axios.get('/api/brands')
                this.brands = res.data
            } catch (err) {
                this.error = err
            } finally {
                this.isLoadingBrands = false
            }
        }
    }
}) 