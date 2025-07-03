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

    return {
        getInventories,
    }
}