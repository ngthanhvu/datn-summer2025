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

    const createStockMovement = async (data) => {
        try {
            const res = await API.post('/api/stock-movement', data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json'
                }
            })
            return res.data
        } catch (err) {
            console.log("Error create stockMovement:".err)
        }
    }

    const getStockMovement = async () => {
        try {
            const res = await API.get('/api/stock-movement')
            return res.data
        } catch (err) {
            console.log("Error get data: ".err)
        }
    }

    return {
        getInventories,
        createStockMovement,
        getStockMovement
    }
}