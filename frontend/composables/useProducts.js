import axios from 'axios'

export const useProducts = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    const getProducts = async () => {
        const response = await API.get('/api/products')
        return response.data
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

    const toggleFavorite = async (productId) => {
        try {
            const response = await API.post(`/api/products/${productId}/favorite`)
            return response.data
        } catch (error) {
            console.error('Error toggling favorite:', error)
            throw error
        }
    }

    const getFavoriteProducts = async () => {
        try {
            const response = await API.get('/api/products/favorites')
            return response.data
        } catch (error) {
            console.error('Error getting favorite products:', error)
            throw error
        }
    }

    const isFavorite = async (productId) => {
        try {
            const response = await API.get(`/api/products/${productId}/favorite`)
            return response.data.is_favorite
        } catch (error) {
            console.error('Error checking favorite status:', error)
            return false
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
        getCategories
    }
}