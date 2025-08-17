import axios from 'axios'
import Cookies from 'js-cookie'
import { ref } from 'vue'
import api from '../utils/api'

export function useSettings() {
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

    if (!apiBaseUrl) {
        console.error('API Base URL is not configured')
        throw new Error('API configuration error')
    }

    const baseUrl = apiBaseUrl.endsWith('/') ? apiBaseUrl : `${apiBaseUrl}/`

    const settings = ref({})
    const isLoading = ref(false)
    const error = ref(null)

    // Sử dụng instance axios chung từ utility
    const API = api

    const getToken = () => {
        // Ưu tiên lấy từ cookie, fallback sang localStorage
        return Cookies.get('token') || localStorage.getItem('token') || null
    }

    const getHeaders = () => {
        const token = getToken()
        return token ? { Authorization: `Bearer ${token}` } : {}
    }

    const fetchSettings = async (withAuth = true) => {
        try {
            isLoading.value = true
            error.value = null

            const headers = withAuth ? getHeaders() : {}
            const response = await API.get('api/settings', { headers })

            settings.value = response.data
            return response.data
        } catch (err) {
            console.error('Error fetching settings:', err)
            error.value = err.response?.data?.error || 'Lỗi tải cài đặt hệ thống'
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const updateSettings = async (payload) => {
        try {
            isLoading.value = true
            error.value = null

            const response = await API.post('api/settings', payload, {
                headers: getHeaders()
            })

            settings.value = { ...settings.value, ...payload }
            return response.data
        } catch (err) {
            console.error('Error updating settings:', err)
            error.value = err.response?.data?.error || 'Không thể cập nhật cài đặt'
            throw error.value
        } finally {
            isLoading.value = false
        }
    }

    return {
        settings,
        isLoading,
        error,
        fetchSettings,
        updateSettings
    }
}

export default useSettings
export const useSetting = useSettings
