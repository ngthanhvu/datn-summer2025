import axios from 'axios'

export const useCategory = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    const getCategories = async () => {
        const response = await API.get('/api/categories')
        return response.data
    }

    const getCategoryById = async (id) => {
        try {
            const response = await API.get(`/api/categories/${id}`)
            return response.data
        } catch (error) {
            console.error('Error getting category:', error)
            throw error
        }
    }

    const createCategory = async (category) => {
        try {
            const response = await API.post('/api/categories', category, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error creating category:', error)
            throw error
        }
    }

    const updateCategory = async (id, category) => {
        try {
            console.log('Sending data to server:', {
                id,
                category: Object.fromEntries(category.entries())
            })

            const response = await API.post(`/api/categories/${id}?_method=PUT`, category, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error updating category:', error.response?.data || error)
            throw error
        }
    }

    const deleteCategory = async (id) => {
        try {
            const response = await API.delete(`/api/categories/${id}`)
            return response.data
        } catch (error) {
            console.error('Error deleting category:', error)
            throw error
        }
    }

    const bulkDeleteCategories = async (ids) => {
        try {
            const response = await API.post('/api/categories/bulk-delete', {
                ids: Array.from(ids)
            })
            return response.data
        } catch (error) {
            console.error('Error bulk deleting categories:', error.response?.data || error)
            throw error
        }
    }


    return {
        getCategories,
        getCategoryById,
        createCategory,
        updateCategory,
        deleteCategory,
        bulkDeleteCategories
    }
}