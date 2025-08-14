import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useProducts } from '../composable/useProducts'

export const useProductStore = defineStore('product', () => {
    const products = ref([])
    const loading = ref(false)
    const error = ref(null)
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 8,
        total: 0,
        from: null,
        to: null,
        next_page_url: null,
        prev_page_url: null,
        links: []
    })

    const { getProducts, searchProducts } = useProducts()

    const fetchProducts = async (filters = {}, page = 1) => {
        if (loading.value) {
            return
        }

        loading.value = true
        error.value = null
        try {
            const result = await getProducts(filters, page)
            products.value = result?.products || []
            pagination.value = result?.pagination || {
                current_page: 1,
                last_page: 1,
                per_page: 8,
                total: 0,
                from: null,
                to: null,
                next_page_url: null,
                prev_page_url: null,
                links: []
            }
        } catch (err) {
            error.value = err
            products.value = []
        } finally {
            loading.value = false
        }
    }

    const searchProductsAction = async (query, filters = {}, page = 1) => {
        if (loading.value) {
            return
        }

        loading.value = true
        error.value = null
        try {
            const result = await searchProducts(query, filters, page)
            products.value = result?.products || []
            pagination.value = result?.pagination || {
                current_page: 1,
                last_page: 1,
                per_page: 8,
                total: 0,
                from: null,
                to: null,
                next_page_url: null,
                prev_page_url: null,
                links: []
            }
        } catch (err) {
            console.error('Store: Search error:', err)
            error.value = err
            products.value = []
        } finally {
            loading.value = false
        }
    }

    return {
        products,
        loading,
        error,
        pagination,
        fetchProducts,
        searchProductsAction
    }
})