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

    const getProductById = async (id) => {
        try {
            const response = await API.get(`/api/products/${id}`)
            return response.data
        } catch (error) {
            console.error('Error getting product:', error)
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

    return {
        getProducts,
        getProductById,
        createProduct,
        updateProduct,
        deleteProduct
    }
}