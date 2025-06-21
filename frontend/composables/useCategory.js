import axios from 'axios'

export const useCategory = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    const getCategories = async () => {
        const response = await API.get('/api/categories')
        const categories = response.data

        return categories
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

    const logCategoryStats = async () => {
        try {
            const categories = await getCategories()

            const totalCategories = categories.length
            const totalProducts = categories.reduce((sum, cat) => sum + (cat.products_count || 0), 0)
            const activeCategories = categories.filter(cat => cat.is_active).length

            const topCategories = categories
                .sort((a, b) => (b.products_count || 0) - (a.products_count || 0))
                .slice(0, 3)

            topCategories.forEach((cat, index) => {
            })

            const emptyCategories = categories.filter(cat => !cat.products_count || cat.products_count === 0)
            if (emptyCategories.length > 0) {
                emptyCategories.forEach(cat => {
                })
            }

            return {
                totalCategories,
                totalProducts,
                activeCategories,
                topCategories,
                emptyCategories
            }
        } catch (error) {
            console.error('❌ Lỗi khi lấy thống kê danh mục:', error)
            return null
        }
    }

    return {
        getCategories,
        getCategoryById,
        createCategory,
        updateCategory,
        deleteCategory,
        bulkDeleteCategories,
        logCategoryStats
    }
}