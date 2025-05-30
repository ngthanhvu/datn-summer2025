import axios from 'axios'

export const useBrand = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    const getBrands = async () => {
        const response = await API.get('/api/brands')
        return response.data
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
            // Log the actual data being sent
            console.log('Sending data to server:', {
                id,
                brand: Object.fromEntries(brand.entries())
            })

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

    return {
        getBrands,
        getBrandById,
        createBrand,
        updateBrand,
        deleteBrand
    }
}