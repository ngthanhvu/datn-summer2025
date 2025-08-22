import axios from 'axios'
import Cookies from 'js-cookie'
import api from '../utils/api'

export const useProducts = () => {
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

    // Sử dụng instance axios chung từ utility
    const API = api

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

    const getProducts = async (filters = {}, page = 1, perPage = 8) => {
        try {
            const cacheKey = `products_${JSON.stringify(filters)}_page_${page}_perPage_${perPage}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const params = new URLSearchParams()

            params.append('page', page)
            params.append('per_page', perPage)

            // Colors
            if (filters.color && Array.isArray(filters.color) && filters.color.length > 0) {
                filters.color.forEach(c => params.append('color[]', c))
            } else if (filters.color && !Array.isArray(filters.color)) {
                params.append('color', filters.color)
            }

            if (filters.min_price) {
                params.append('min_price', filters.min_price)
            }
            if (filters.max_price) {
                params.append('max_price', filters.max_price)
            }

            // Categories
            if (filters.category) {
                if (Array.isArray(filters.category)) {
                    filters.category.forEach(id => params.append('category[]', id))
                } else {
                    params.append('category', filters.category)
                }
            }

            // Brands
            if (filters.brand) {
                if (Array.isArray(filters.brand)) {
                    filters.brand.forEach(id => params.append('brand[]', id))
                } else {
                    params.append('brand', filters.brand)
                }
            }

            // Sizes
            if (filters.size && Array.isArray(filters.size) && filters.size.length > 0) {
                filters.size.forEach(s => params.append('size[]', s))
            } else if (filters.size && !Array.isArray(filters.size)) {
                params.append('size', filters.size)
            }

            if (filters.sort_by) {
                params.append('sort_by', filters.sort_by)
                params.append('sort_direction', filters.sort_direction || 'asc')
            }

            const response = await API.get(`/api/products?${params.toString()}`)

            const result = {
                products: response.data.data,
                pagination: {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    per_page: response.data.per_page,
                    total: response.data.total,
                    from: response.data.from,
                    to: response.data.to
                }
            }

            setCachedData(cacheKey, result)
            return result
        } catch (error) {
            console.error('Error getting products:', error)
            throw error
        }
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

    const getCategories = async () => {
        try {
            const cacheKey = 'categories'
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get('/api/categories')
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error getting categories:', error)
            return []
        }
    }

    const getVariant = async () => {
        try {
            const cacheKey = 'variants'
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const res = await API.get('/api/variants')
            setCachedData(cacheKey, res.data)
            return res.data
        } catch (err) {
            console.error('Bug:', err)
            return []
        }
    }

    const getProductById = async (id) => {
        try {
            const cacheKey = `product_${id}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const token = getTokenFromCookie()
            const headers = token ? { Authorization: `Bearer ${token}` } : {}

            const response = await API.get(`/api/products/${id}`, { headers })
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error getting product:', error)
            throw error
        }
    }

    const getProductBySlug = async (slug) => {
        try {
            const cacheKey = `product_slug_${slug}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get(`/api/products/slug/${slug}`)
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error getting product by slug:', error)
            throw error
        }
    }

    const createProduct = async (product) => {
        try {
            const token = getTokenFromCookie()
            const headers = {
                'Content-Type': 'multipart/form-data',
                'Accept': 'application/json'
            }
            if (token) {
                headers.Authorization = `Bearer ${token}`
            }

            const response = await API.post('/api/products', product, { headers })
            return response.data
        } catch (error) {
            console.error('Error creating product:', error)
            throw error
        }
    }

    const updateProduct = async (id, product) => {
        try {
            const token = getTokenFromCookie()
            const headers = {
                'Content-Type': 'multipart/form-data',
                'Accept': 'application/json'
            }
            if (token) {
                headers.Authorization = `Bearer ${token}`
            }

            const response = await API.post(`/api/products/${id}?_method=PUT`, product, { headers })
            return response.data
        } catch (error) {
            console.error('Error updating product:', error)
            throw error
        }
    }

    const deleteProduct = async (id) => {
        try {
            const response = await API.delete(`/api/products/${id}`)
            return response.data
        } catch (error) {
            console.error('Error deleting product:', error)
            throw error
        }
    }

    const bulkDeleteProducts = async (ids) => {
        try {
            const response = await API.delete('/api/products/delete/bulk-delete', {
                data: { ids }
            })
            return response.data
        } catch (error) {
            console.error('Error bulk deleting products:', error)
            throw error
        }
    }

    const getTokenFromCookie = () => {
        const cookie = document.cookie
            .split('; ')
            .find(row => row.startsWith('token='))
        return cookie ? cookie.split('=')[1] : null
    }

    const toggleFavorite = async (productSlug) => {
        try {
            const token = getTokenFromCookie()
            if (!token) throw new Error('Bạn chưa đăng nhập')

            const favorites = await getFavoriteProducts()
            const exists = favorites.find(item => item.product_slug === productSlug)

            if (exists) {
                await API.delete(`/api/favorites/${productSlug}`, {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                })
                return false
            } else {
                await API.post('/api/favorites', { product_slug: productSlug }, {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                })
                return true
            }
        } catch (error) {
            throw error
        }
    }

    const getFavoriteProducts = async () => {
        try {
            const token = getTokenFromCookie()
            if (!token) return []

            const response = await API.get('/api/favorites', {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            })
            return response.data
        } catch (error) {
            return []
        }
    }

    const isFavorite = async (productSlug) => {
        try {
            const token = getTokenFromCookie()
            if (!token) return false

            const response = await API.get(`/api/favorites/check/${productSlug}`, {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            })
            return response.data.is_favorite
        } catch (error) {
            return false
        }
    }

    const getFilterOptions = async () => {
        try {
            const response = await API.get('/api/products/filter-options')
            return response.data
        } catch (error) {
            console.error('Error getting filter options:', error)
            return null
        }
    }

    const searchProducts = async (query, filters = {}, page = 1) => {
        try {
            const params = new URLSearchParams()
            if (query) {
                params.append('q', query)
            }
            if (page) {
                params.append('page', page)
            }
            if (filters.color && filters.color.length > 0) {
                if (Array.isArray(filters.color)) {
                    filters.color.forEach(c => params.append('color[]', c));
                } else {
                    params.append('color', filters.color);
                }
            }
            if (filters.min_price) {
                params.append('min_price', filters.min_price)
            }
            if (filters.max_price) {
                params.append('max_price', filters.max_price)
            }
            if (filters.category && filters.category.length > 0) {
                if (Array.isArray(filters.category)) {
                    filters.category.forEach(c => params.append('category[]', c));
                } else {
                    params.append('category', filters.category);
                }
            }
            if (filters.brand && filters.brand.length > 0) {
                if (Array.isArray(filters.brand)) {
                    filters.brand.forEach(b => params.append('brand[]', b));
                } else {
                    params.append('brand', filters.brand);
                }
            }
            if (filters.size && filters.size.length > 0) {
                if (Array.isArray(filters.size)) {
                    filters.size.forEach(s => params.append('size[]', s));
                } else {
                    params.append('size', filters.size);
                }
            }

            if (filters.sort_by) {
                params.append('sort_by', filters.sort_by)
                params.append('sort_direction', filters.sort_direction || 'asc')
            }

            const response = await API.get(`/api/products/search?${params.toString()}`)

            if (response.data && response.data.data) {
                return {
                    products: response.data.data,
                    pagination: {
                        current_page: response.data.current_page || 1,
                        last_page: response.data.last_page || 1,
                        per_page: response.data.per_page || 8,
                        total: response.data.total || 0,
                        from: response.data.from || null,
                        to: response.data.to || null
                    }
                }
            } else if (Array.isArray(response.data)) {
                return {
                    products: response.data,
                    pagination: {
                        current_page: 1,
                        last_page: 1,
                        per_page: response.data.length,
                        total: response.data.length,
                        from: 1,
                        to: response.data.length
                    }
                }
            } else {
                return {
                    products: [],
                    pagination: {
                        current_page: 1,
                        last_page: 1,
                        per_page: 8,
                        total: 0,
                        from: null,
                        to: null
                    }
                }
            }
        } catch (error) {
            console.error('Error searching products:', error)
            return {
                products: [],
                pagination: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 8,
                    total: 0,
                    from: null,
                    to: null
                }
            }
        }
    }

    const getTemplateSheet = async () => {
        try {
            const res = await API.get('/api/products/import/template', {
                responseType: 'blob',
            })
            return res.data
        } catch (err) {
            console.error('Error fetch template', err)
        }
    }

    const importFile = async (file) => {
        try {
            const res = await API.post('/api/products/import', file, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json'
                }
            })
            return res.data
        } catch (err) {
            console.log('Error import:', err)
        }
    }

    const getNewProducts = async (limit = 10) => {
        try {
            const cacheKey = `new_products_${limit}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get('/api/products', {
                params: {
                    sort_by: 'created_at',
                    sort_direction: 'desc',
                    limit: limit
                }
            })
            setCachedData(cacheKey, response.data)
            return response.data.data
        } catch (error) {
            console.error('Error getting new products:', error)
            return []
        }
    }

    const getRecommendedProducts = async () => {
        try {
            const userCookie = Cookies.get('user')
            let params = {}

            if (userCookie) {
                const user = typeof userCookie === 'string' ? JSON.parse(userCookie) : userCookie
                if (user.gender) params.gender = user.gender
                if (user.dateOfBirth) params.dateOfBirth = user.dateOfBirth
                if (user.address) params.address = user.address
            }

            const response = await API.get('/api/products/recommend', { params })
            return response.data
        } catch (error) {
            console.error('Error getting recommended products:', error)
            return []
        }
    }

    const getBestSellerProducts = async (limit = 10) => {
        try {
            const cacheKey = `bestseller_products_${limit}`
            const cached = getCachedData(cacheKey)
            if (cached) {
                return cached
            }

            const response = await API.get('/api/products/bestsellers', {
                params: { limit }
            })
            setCachedData(cacheKey, response.data)
            return response.data
        } catch (error) {
            console.error('Error getting bestseller products:', error)
            return []
        }
    }

    return {
        getProducts,
        getProductById,
        getProductBySlug,
        createProduct,
        updateProduct,
        deleteProduct,
        toggleFavorite,
        getFavoriteProducts,
        isFavorite,
        getBrands,
        getCategories,
        getFilterOptions,
        searchProducts,
        getTemplateSheet,
        importFile,
        bulkDeleteProducts,
        getVariant,
        getNewProducts,
        getRecommendedProducts
        , getBestSellerProducts
    }
}