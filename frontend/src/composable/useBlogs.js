import axios from 'axios'
import { ref } from 'vue'
import Cookies from 'js-cookie'
import api from '../utils/api'

export const useBlog = () => {
    const token = Cookies.get('token')
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

    // Sử dụng instance axios chung từ utility
    const API = api

    const blogs = ref([])
    const blog = ref(null)
    const loading = ref(false)
    const error = ref(null)
    const pagination = ref(null)
    const categories = ref([])
    const categoriesLoading = ref(false)
    const relatedBlogs = ref([])
    const relatedBlogsLoading = ref(false)

    const fetchBlogs = async (page = 1, filters = {}) => {
        loading.value = true
        error.value = null
        try {
            const params = { page, ...filters }
            const res = await API.get('/api/blogs', { params })

            if (res.data.success) {
                blogs.value = res.data.data.data
                pagination.value = {
                    current_page: res.data.data.current_page,
                    last_page: res.data.data.last_page,
                    per_page: res.data.data.per_page,
                    total: res.data.data.total,
                    from: res.data.data.from,
                    to: res.data.data.to
                }
                return res.data
            } else {
                throw new Error(res.data.message || 'Failed to fetch blogs')
            }
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'An error occurred'
            throw err
        } finally {
            loading.value = false
        }
    }

    const fetchBlog = async (id) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.get(`/api/blogs/${id}`)
            if (res.data.success) {
                blog.value = res.data.data
            } else {
                blog.value = null
                throw new Error(res.data.message || 'Blog not found')
            }
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'Blog not found'
            blog.value = null
        } finally {
            loading.value = false
        }
    }

    const fetchBlogBySlug = async (slug) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.get(`/api/blogs/slug/${slug}`)
            if (res.data.success) {
                blog.value = res.data.data
            } else {
                blog.value = null
                throw new Error(res.data.message || 'Blog not found')
            }
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'Blog not found'
            blog.value = null
        } finally {
            loading.value = false
        }
    }

    const fetchRelatedBlogs = async (categoryId, currentBlogId, limit = 5) => {
        relatedBlogsLoading.value = true
        try {
            const params = {
                category_id: categoryId,
                limit,
                exclude: currentBlogId
            }
            const res = await API.get('/api/blogs', { params })
            if (res.data.success) {
                relatedBlogs.value = res.data.data.data || []
            }
        } catch (err) {
            console.error('Failed to fetch related blogs:', err)
            relatedBlogs.value = []
        } finally {
            relatedBlogsLoading.value = false
        }
    }

    const fetchCategories = async () => {
        categoriesLoading.value = true
        try {
            const res = await API.get('/api/blog-categories')
            if (res.data.success) {
                categories.value = res.data.data
            }
        } catch (err) {
            console.error('Failed to fetch categories:', err)
        } finally {
            categoriesLoading.value = false
        }
    }

    const handleError = (err) => {
        const errorMessage = err.response?.data?.message || err.message || 'An error occurred'
        const validationErrors = err.response?.data?.errors || null
        error.value = errorMessage
        const errorObj = new Error(errorMessage)
        if (validationErrors) errorObj.errors = validationErrors
        throw errorObj
    }

    const createBlog = async (blogData) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.post('/api/blogs', blogData, {
                headers: { Authorization: `Bearer ${token}` }
            })
            if (res.data.success) return res.data
            throw new Error(res.data.message || 'Failed to create blog')
        } catch (err) {
            handleError(err)
        } finally {
            loading.value = false
        }
    }

    const updateBlog = async (id, formData) => {
        loading.value = true
        error.value = null
        try {
            formData.append('_method', 'PUT')
            const res = await API.post(`/api/blogs/${id}`, formData, {
                headers: { Authorization: `Bearer ${token}` }
            })
            if (res.data.success) return res.data
            throw new Error(res.data.message || 'Failed to update blog')
        } catch (err) {
            handleError(err)
        } finally {
            loading.value = false
        }
    }

    const updateBlogJson = async (id, blogData) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.put(`/api/blogs/${id}`, blogData, {
                headers: {
                    'Content-Type': 'application/json',
                    Authorization: `Bearer ${token}`
                }
            })
            if (res.data.success) return res.data
            throw new Error(res.data.message || 'Failed to update blog')
        } catch (err) {
            handleError(err)
        } finally {
            loading.value = false
        }
    }

    const deleteBlog = async (id) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.delete(`/api/blogs/${id}`)
            if (res.data.success) return res.data
            throw new Error(res.data.message || 'Failed to delete blog')
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'An error occurred'
            throw err
        } finally {
            loading.value = false
        }
    }

    const createCategory = async (categoryData) => {
        try {
            const res = await API.post('/api/blog-categories', categoryData, {
                headers: { Authorization: `Bearer ${token}` }
            })
            if (res.data.success) {
                await fetchCategories()
                return res.data
            }
            throw new Error(res.data.message || 'Failed to create category')
        } catch (err) {
            handleError(err)
        }
    }

    const updateCategory = async (id, categoryData) => {
        try {
            const res = await API.put(`/api/blog-categories/${id}`, categoryData, {
                headers: { Authorization: `Bearer ${token}` }
            })
            if (res.data.success) {
                await fetchCategories()
                return res.data
            }
            throw new Error(res.data.message || 'Failed to update category')
        } catch (err) {
            handleError(err)
        }
    }

    const deleteCategory = async (id) => {
        try {
            const res = await API.delete(`/api/blog-categories/${id}`)
            if (res.data.success) {
                await fetchCategories()
                return res.data
            }
            throw new Error(res.data.message || 'Failed to delete category')
        } catch (err) {
            handleError(err)
        }
    }

    const clearError = () => { error.value = null }
    const resetState = () => {
        blogs.value = []
        blog.value = null
        loading.value = false
        error.value = null
        pagination.value = null
        categories.value = []
        categoriesLoading.value = false
        relatedBlogs.value = []
        relatedBlogsLoading.value = false
    }

    return {
        blogs,
        blog,
        loading,
        error,
        pagination,
        categories,
        categoriesLoading,
        relatedBlogs,
        relatedBlogsLoading,
        fetchBlogs,
        fetchBlog,
        fetchBlogBySlug,
        createBlog,
        updateBlog,
        updateBlogJson,
        deleteBlog,
        fetchCategories,
        fetchRelatedBlogs,
        createCategory,
        updateCategory,
        deleteCategory,
        clearError,
        resetState
    }
}
