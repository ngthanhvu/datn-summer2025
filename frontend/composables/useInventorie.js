import axios from 'axios'

export const useInventories = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl
    const token = useCookie('token')

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    API.interceptors.request.use(async (config) => {
        if (token.value) {
            config.headers.Authorization = `Bearer ${token.value}`
        }
        return config
    })

    const getInventories = async (params) => {
        try {
            const response = await API.get('/api/inventory', { params })
            return response.data
        } catch (error) {
            console.error('Error fetching inventories:', error)
            throw error
        }
    }

    const updateStock = async (data) => {
        try {
            const response = await API.post('/api/inventory/update', data)
            return response.data
        } catch (error) {
            console.error('Error updating stock:', error)
            throw error
        }
    }

    const getMovements = async (params) => {
        try {
            const response = await API.get('/api/inventory/movements', { params })
            return response.data
        } catch (error) {
            console.error('Error fetching movements:', error)
            throw error
        }
    }

    const getVariants = async () => {
        try {
            const response = await API.get('/api/variants')
            return response.data
        } catch (error) {
            console.error('Error fetching variants:', error)
            throw error
        }
    }

    return {
        getInventories,
        updateStock,
        getMovements,
        getVariants
    }
}