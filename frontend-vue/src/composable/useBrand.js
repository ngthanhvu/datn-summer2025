// composables/useBrand.js
import axios from 'axios'

const API = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    timeout: 10000
})

export const useBrand = () => {
    const getBrands = async () => {
        try {
            const response = await API.get('/api/brands')
            return response.data
        } catch (error) {
            console.error('Error getting brands:', error)
            return []
        }
    }

    const getBrandById = async (id) => {
        try {
            const response = await API.get(`/api/brands/${id}`)
            return response.data
        } catch (error) {
            console.error('Error getting brand:', error)
            throw error
        }
    }

    const createBrand = async (brand) => {
        try {
            const response = await API.post('/api/brands', brand)
            return response.data
        } catch (error) {
            console.error('Error creating brand:', error)
            throw error
        }
    }

    const updateBrand = async (id, brand) => {
        try {
            const response = await API.post(`/api/brands/${id}?_method=PUT`, brand, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error updating brand:', error.response?.data || error)
            throw error
        }
    }

    const deleteBrand = async (id) => {
        try {
            const response = await API.delete(`/api/brands/${id}`)
            return response.data
        } catch (error) {
            console.error('Error deleting brand:', error)
            throw error
        }
    }

    const bulkDeleteBrands = async (ids) => {
        try {
            const response = await API.post('/api/brands/bulk-delete', {
                ids: Array.from(ids)
            })
            return response.data
        } catch (error) {
            console.error('Error bulk deleting brands:', error.response?.data || error)
            throw error
        }
    }

    return {
        getBrands,
        getBrandById,
        createBrand,
        updateBrand,
        deleteBrand,
        bulkDeleteBrands
    }
}
