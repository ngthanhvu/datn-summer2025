import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

export const useProductStore = defineStore('product', () => {
    const products = ref([])
    const loading = ref(false)
    const error = ref(null)

    const API = axios.create({
        baseURL: apiBaseUrl,
    })

    const fetchProducts = async (filters = {}) => {
        loading.value = true
        error.value = null
        try {
            const params = new URLSearchParams()
            // (Có thể thêm filter nếu cần)
            const response = await API.get(`/api/products?${params.toString()}`)
            products.value = response.data
        } catch (err) {
            error.value = err
        } finally {
            loading.value = false
        }
    }

    return {
        products,
        loading,
        error,
        fetchProducts
    }
})