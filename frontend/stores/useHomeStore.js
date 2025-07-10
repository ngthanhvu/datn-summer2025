import { defineStore } from 'pinia'
import axios from 'axios'

export const useHomeStore = defineStore('homeStore', {
    state: () => ({
        // Categories
        categories: [],
        isLoadingCategories: false,

        // Products
        newProducts: [],
        categoryProducts: [],
        isLoadingProducts: false,

        // Brands
        brands: [],
        isLoadingBrands: false,

        // Reviews
        latestReviews: [],
        isLoadingReviews: false,

        // Coupons
        coupons: [],
        isLoadingCoupons: false,

        // Error handling
        error: null,

        // Cache timestamps
        lastFetch: {
            categories: 0,
            products: 0,
            brands: 0,
            reviews: 0,
            coupons: 0
        }
    }),

    getters: {
        // Check if data is stale (older than 5 minutes)
        isDataStale: (state) => (key) => {
            const now = Date.now()
            const fiveMinutes = 5 * 60 * 1000
            return now - state.lastFetch[key] > fiveMinutes
        },

        // Check if we have data and it's not stale
        hasValidData: (state) => (key) => {
            const dataMap = {
                categories: state.categories,
                products: state.newProducts,
                brands: state.brands,
                reviews: state.latestReviews,
                coupons: state.coupons
            }
            return dataMap[key] && dataMap[key].length > 0 && !state.isDataStale(key)
        }
    },

    actions: {
        // Fetch categories with cache
        async fetchCategories() {
            if (this.hasValidData('categories')) {
                return this.categories
            }

            this.isLoadingCategories = true
            this.error = null

            try {
                const res = await axios.get('/api/categories')
                this.categories = res.data
                this.lastFetch.categories = Date.now()
                return res.data
            } catch (err) {
                this.error = err
                console.error('Error fetching categories:', err)
                return []
            } finally {
                this.isLoadingCategories = false
            }
        },

        // Fetch new products with cache
        async fetchNewProducts(limit = 10) {
            if (this.hasValidData('products')) {
                return this.newProducts
            }

            this.isLoadingProducts = true
            this.error = null

            try {
                const res = await axios.get('/api/products', {
                    params: {
                        sort_by: 'created_at',
                        sort_direction: 'desc',
                        limit: limit
                    }
                })
                this.newProducts = res.data
                this.lastFetch.products = Date.now()
                return res.data
            } catch (err) {
                this.error = err
                console.error('Error fetching new products:', err)
                return []
            } finally {
                this.isLoadingProducts = false
            }
        },

        // Fetch products by category
        async fetchCategoryProducts(categoryId = null, limit = 20) {
            this.isLoadingProducts = true
            this.error = null

            try {
                const params = { limit }
                if (categoryId) {
                    params.category = categoryId
                }

                const res = await axios.get('/api/products', { params })
                this.categoryProducts = res.data
                return res.data
            } catch (err) {
                this.error = err
                console.error('Error fetching category products:', err)
                return []
            } finally {
                this.isLoadingProducts = false
            }
        },

        // Fetch brands with cache
        async fetchBrands() {
            if (this.hasValidData('brands')) {
                return this.brands
            }

            this.isLoadingBrands = true
            this.error = null

            try {
                const res = await axios.get('/api/brands')
                this.brands = res.data
                this.lastFetch.brands = Date.now()
                return res.data
            } catch (err) {
                this.error = err
                console.error('Error fetching brands:', err)
                return []
            } finally {
                this.isLoadingBrands = false
            }
        },

        // Fetch latest reviews with cache
        async fetchLatestReviews(limit = 6) {
            if (this.hasValidData('reviews')) {
                return this.latestReviews
            }

            this.isLoadingReviews = true
            this.error = null

            try {
                const res = await axios.get('/api/reviews/latest', {
                    params: { per_page: limit }
                })
                this.latestReviews = res.data.data || res.data
                this.lastFetch.reviews = Date.now()
                return this.latestReviews
            } catch (err) {
                this.error = err
                console.error('Error fetching latest reviews:', err)
                return []
            } finally {
                this.isLoadingReviews = false
            }
        },

        // Fetch coupons with cache
        async fetchCoupons() {
            if (this.hasValidData('coupons')) {
                return this.coupons
            }

            this.isLoadingCoupons = true
            this.error = null

            try {
                const res = await axios.get('/api/coupons')
                this.coupons = res.data
                this.lastFetch.coupons = Date.now()
                return res.data
            } catch (err) {
                this.error = err
                console.error('Error fetching coupons:', err)
                return []
            } finally {
                this.isLoadingCoupons = false
            }
        },

        // Fetch all home data in parallel
        async fetchAllHomeData() {
            try {
                const [
                    categories,
                    newProducts,
                    brands,
                    latestReviews,
                    coupons
                ] = await Promise.all([
                    this.fetchCategories(),
                    this.fetchNewProducts(10),
                    this.fetchBrands(),
                    this.fetchLatestReviews(6),
                    this.fetchCoupons()
                ])

                return {
                    categories,
                    newProducts,
                    brands,
                    latestReviews,
                    coupons
                }
            } catch (err) {
                console.error('Error fetching all home data:', err)
                return {}
            }
        },

        // Clear cache for specific data type
        clearCache(type) {
            if (type) {
                this.lastFetch[type] = 0
            } else {
                // Clear all cache
                Object.keys(this.lastFetch).forEach(key => {
                    this.lastFetch[key] = 0
                })
            }
        },

        // Reset store
        reset() {
            this.categories = []
            this.newProducts = []
            this.categoryProducts = []
            this.brands = []
            this.latestReviews = []
            this.coupons = []
            this.error = null
            this.clearCache()
        }
    }
}) 