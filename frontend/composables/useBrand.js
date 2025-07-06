import axios from 'axios'

export const useBrand = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl,
        timeout: 10000 // Thêm timeout 10 giây
    })

    // Cache system
    const cache = new Map()
    const CACHE_DURATION = 5 * 60 * 1000 // 5 phút

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

    const clearCache = () => {
        cache.clear()
    }

    const getBrands = async () => {
        try {
            const cacheKey = 'brands'
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get('/api/brands')
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error getting brands:', error)
            return []
        }
    }

    const getBrandById = async (id) => {
        try {
            const cacheKey = `brand_${id}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get(`/api/brands/${id}`)
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error getting brand:', error)
            throw error
        }
    }

    const createBrand = async (brand) => {
        try {
            const response = await API.post('/api/brands', brand)
            // Clear cache khi có thay đổi
            clearCache()
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
            // Clear cache khi có thay đổi
            clearCache()
            return response.data
        } catch (error) {
            console.error('Error updating brand:', error.response?.data || error)
            throw error
        }
    }

    const deleteBrand = async (id) => {
        try {
            const response = await API.delete(`/api/brands/${id}`)
            // Clear cache khi có thay đổi
            clearCache()
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
            // Clear cache khi có thay đổi
            clearCache()
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
        bulkDeleteBrands,
        clearCache
    }
}