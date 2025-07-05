import axios from 'axios'

export const useInventories = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl
    const token = useCookie('token')

    const API = axios.create({
        baseURL: apiBaseUrl,
        timeout: 10000 // Thêm timeout 10 giây
    })

    API.interceptors.request.use(async (config) => {
        if (token.value) {
            config.headers.Authorization = `Bearer ${token.value}`
        }
        return config
    })

    // Cache cho inventories
    const cache = new Map()
    const CACHE_DURATION = 2 * 60 * 1000 // 2 phút

    const getCachedData = (key) => {
        const cached = cache.get(key)
        if (cached && Date.now() - cached.timestamp < CACHE_DURATION) {
            return cached.data
        }
        return null
    }

    const setCachedData = (key, data) => {
        cache.set(key, {
            data,
            timestamp: Date.now()
        })
    }

    const getInventories = async (params) => {
        try {
            const cacheKey = `inventories_${JSON.stringify(params)}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get('/api/inventory', { params })
            setCachedData(cacheKey, response.data)
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
            // Clear cache khi có thay đổi
            cache.clear()
            return res.data
        } catch (err) {
            console.log("Error create stockMovement:".err)
            throw err
        }
    }

    const getStockMovement = async () => {
        try {
            const cacheKey = 'stock_movements'
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const res = await API.get('/api/stock-movement')
            setCachedData(cacheKey, res.data)
            return res.data
        } catch (err) {
            console.log("Error get data: ".err)
            return []
        }
    }

    return {
        getInventories,
        createStockMovement,
        getStockMovement
    }
}