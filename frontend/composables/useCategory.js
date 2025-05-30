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

    const createCategory = async (category) => {
        try {
            const response = await API.post('/api/categories', category, {
                headers: {
                    'Content-Type': 'multipart/form-data'
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
            const response = await API.put(`/api/categories/${id}`, category)
            return response.data
        } catch (error) {
            console.error('Error updating category:', error)
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

    return {
        getCategories,
        createCategory,
        updateCategory,
        deleteCategory
    }
}