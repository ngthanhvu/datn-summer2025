import axios from 'axios'
import { ref } from 'vue'
import Cookies from 'js-cookie'
import api from '../utils/api'

export const usePages = () => {
    const token = Cookies.get('token')
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

    // Sử dụng instance axios chung từ utility
    const API = api

    const pages = ref([])
    const page = ref(null)
    const loading = ref(false)
    const error = ref(null)
    const pagination = ref(null)

    const fetchPages = async (page = 1, filters = {}) => {
        loading.value = true
        error.value = null
        try {
            const params = { page, ...filters }
            const res = await API.get('/api/pages', { params })

            if (res.data.success) {
                pages.value = res.data.data.data
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
                throw new Error(res.data.message || 'Failed to fetch pages')
            }
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'An error occurred'
            throw err
        } finally {
            loading.value = false
        }
    }

    const fetchPage = async (id) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.get(`/api/pages/${id}`)
            if (res.data.success) {
                page.value = res.data.data
            } else {
                page.value = null
                throw new Error(res.data.message || 'Page not found')
            }
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'Page not found'
            page.value = null
        } finally {
            loading.value = false
        }
    }

    const fetchPageBySlug = async (slug) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.get(`/api/pages/slug/${slug}`)
            if (res.data.success) {
                page.value = res.data.data
            } else {
                page.value = null
                throw new Error(res.data.message || 'Page not found')
            }
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'Page not found'
            page.value = null
        } finally {
            loading.value = false
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

    const createPage = async (pageData) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.post('/api/pages', pageData, {
                headers: { Authorization: `Bearer ${token}` }
            })
            if (res.data.success) return res.data
            throw new Error(res.data.message || 'Failed to create page')
        } catch (err) {
            handleError(err)
        } finally {
            loading.value = false
        }
    }

    const updatePage = async (id, pageData) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.put(`/api/pages/${id}`, pageData, {
                headers: {
                    'Content-Type': 'application/json',
                    Authorization: `Bearer ${token}`
                }
            })
            if (res.data.success) return res.data
            throw new Error(res.data.message || 'Failed to update page')
        } catch (err) {
            handleError(err)
        } finally {
            loading.value = false
        }
    }

    const deletePage = async (id) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.delete(`/api/pages/${id}`)
            if (res.data.success) return res.data
            throw new Error(res.data.message || 'Failed to delete page')
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'An error occurred'
            throw err
        } finally {
            loading.value = false
        }
    }

    const updatePageStatus = async (id, status) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.put(`/api/pages/${id}/status`, { status })
            if (res.data.success) return res.data
            throw new Error(res.data.message || 'Failed to update page status')
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'An error occurred'
            throw err
        } finally {
            loading.value = false
        }
    }

    const getPagesByType = async (type) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.get(`/api/pages/type/${type}`)
            if (res.data.success) return res.data
            throw new Error(res.data.message || 'Failed to fetch pages by type')
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'An error occurred'
            throw err
        } finally {
            loading.value = false
        }
    }

    const clearError = () => { error.value = null }
    const resetState = () => {
        pages.value = []
        page.value = null
        loading.value = false
        error.value = null
        pagination.value = null
    }

    return {
        pages,
        page,
        loading,
        error,
        pagination,
        fetchPages,
        fetchPage,
        fetchPageBySlug,
        createPage,
        updatePage,
        deletePage,
        updatePageStatus,
        getPagesByType,
        clearError,
        resetState
    }
} 