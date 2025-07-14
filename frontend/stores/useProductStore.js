import { defineStore } from 'pinia'
import axios from 'axios'

export const useProductStore = defineStore('productStore', {
    state: () => ({
        products: [],
        categories: [],
        brands: [],
        variants: [],

        isLoadingProducts: false,
        isLoadingCategories: false,
        isLoadingBrands: false,
        isLoadingVariants: false
    }),

    actions: {
        async fetchProducts(filters = {}) {
            this.isLoadingProducts = true
            try {
                const params = new URLSearchParams()

                if (filters.color) params.append('color', filters.color)
                if (filters.min_price) params.append('min_price', filters.min_price)
                if (filters.max_price) params.append('max_price', filters.max_price)
                if (filters.category) {
                    if (Array.isArray(filters.category)) {
                        filters.category.forEach(id => params.append('category[]', id))
                    } else {
                        params.append('category', filters.category)
                    }
                }
                if (filters.brand) {
                    if (Array.isArray(filters.brand)) {
                        filters.brand.forEach(id => params.append('brand[]', id))
                    } else {
                        params.append('brand', filters.brand)
                    }
                }
                if (filters.sort_by) {
                    params.append('sort_by', filters.sort_by)
                    params.append('sort_direction', filters.sort_direction || 'asc')
                }

                const res = await axios.get(`/api/products?${params.toString()}`)
                this.products = res.data
            } catch (err) {
                console.error('Error fetching products:', err)
            } finally {
                this.isLoadingProducts = false
            }
        },

        async fetchCategories() {
            this.isLoadingCategories = true
            try {
                const res = await axios.get('/api/categories')
                this.categories = res.data
            } catch (err) {
                console.error('Error fetching categories:', err)
            } finally {
                this.isLoadingCategories = false
            }
        },

        async fetchBrands() {
            this.isLoadingBrands = true
            try {
                const res = await axios.get('/api/brands')
                this.brands = res.data
            } catch (err) {
                console.error('Error fetching brands:', err)
            } finally {
                this.isLoadingBrands = false
            }
        },

        async fetchVariants() {
            this.isLoadingVariants = true
            try {
                const res = await axios.get('/api/variants')
                this.variants = res.data
            } catch (err) {
                console.error('Error fetching variants:', err)
            } finally {
                this.isLoadingVariants = false
            }
        }
    }
})
