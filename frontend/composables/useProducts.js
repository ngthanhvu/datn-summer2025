import axios from 'axios'

export const useProducts = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    const getProducts = async (filters = {}) => {
        try {
            const params = new URLSearchParams()

            if (filters.color) {
                params.append('color', filters.color)
            }
            if (filters.min_price) {
                params.append('min_price', filters.min_price)
            }
            if (filters.max_price) {
                params.append('max_price', filters.max_price)
            }
            if (filters.category) {
                params.append('category', filters.category)
            }
            if (filters.brand) {
                params.append('brand', filters.brand)
            }
            if (filters.sort_by) {
                params.append('sort_by', filters.sort_by)
                params.append('sort_direction', filters.sort_direction || 'asc')
            }

            const response = await API.get(`/api/products?${params.toString()}`)
            return response.data
        } catch (error) {
            console.error('Error getting products:', error)
            throw error
        }
    }

    const getBrands = async () => {
        const response = await API.get('/api/brands')
        return response.data
    }

    const getCategories = async () => {
        const response = await API.get('/api/categories')
        return response.data
    }

    const getProductById = async (id) => {
        try {
            const response = await API.get(`/api/products/${id}`)
            return response.data
        } catch (error) {
            console.error('Error getting product:', error)
            throw error
        }
    }

    const getProductBySlug = async (slug) => {
        try {
            const response = await API.get(`/api/products/slug/${slug}`)
            return response.data
        } catch (error) {
            console.error('Error getting product by slug:', error)
            throw error
        }
    }

    const createProduct = async (product) => {
        try {
            const response = await API.post('/api/products', product, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error creating product:', error)
            throw error
        }
    }

    const updateProduct = async (id, product) => {
        try {
            const response = await API.post(`/api/products/${id}?_method=PUT`, product, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json'
                }
            })
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

    const searchProducts = async (query, filters = {}) => {
        try {
            const params = new URLSearchParams()
            if (query) {
                params.append('q', query)
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

            const response = await API.get(`/api/products/search?${params.toString()}`)
            return Array.isArray(response.data) ? response.data : []
        } catch (error) {
            console.error('Error searching products:', error)
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
        searchProducts
    }
}