import axios from 'axios'

const API = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    timeout: 10000,
    headers: {
        Accept: 'application/json'
    }
})

export const useCategories = () => {
    const getCategories = async () => {
        try {
            const response = await API.get('/api/categories')
            return response.data
        } catch (error) {
            console.error('❌ Error getting categories:', error)
            return []
        }
    }

    const getCategoryById = async (id) => {
        try {
            const response = await API.get(`/api/categories/${id}`)
            return response.data
        } catch (error) {
            console.error('❌ Error getting category by ID:', error)
            throw error
        }
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
            console.error('❌ Error creating category:', error)
            throw error
        }
    }

    const updateCategory = async (id, category) => {
        try {
            const response = await API.post(`/api/categories/${id}?_method=PUT`, category, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            return response.data
        } catch (error) {
            console.error('❌ Error updating category:', error)
            throw error
        }
    }

    const deleteCategory = async (id) => {
        try {
            const response = await API.delete(`/api/categories/${id}`)
            return response.data
        } catch (error) {
            console.error('❌ Error deleting category:', error)
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
            console.error('❌ Error bulk deleting categories:', error)
            throw error
        }
    }

    const logCategoryStats = async () => {
        try {
            const categories = await getCategories()
            const totalCategories = categories.length
            const totalProducts = categories.reduce((sum, cat) => sum + (cat.products_count || 0), 0)
            const activeCategories = categories.filter(cat => cat.is_active).length
            const topCategories = [...categories]
                .sort((a, b) => (b.products_count || 0) - (a.products_count || 0))
                .slice(0, 3)
            const emptyCategories = categories.filter(cat => !cat.products_count || cat.products_count === 0)

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
