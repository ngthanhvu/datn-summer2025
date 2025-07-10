import axios from 'axios'
import { ref } from 'vue'
import { useCookie } from '#app'

export const useSettings = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    if (!apiBaseUrl) {
        console.error('API Base URL is not configured')
        throw new Error('API configuration error')
    }

    const baseUrl = apiBaseUrl.endsWith('/') ? apiBaseUrl : `${apiBaseUrl}/`
    const settings = ref({})
    const isLoading = ref(false)
    const error = ref(null)

    const API = axios.create({
        baseURL: baseUrl,
        timeout: 10000,
        withCredentials: true
    })

    const getToken = () => {
        let token = null
        if (process.client) {
            token = useCookie('token').value || localStorage.getItem('token')
        } else {
            token = useCookie('token').value
        }
        return token
    }

    const getHeaders = () => {
        const headers = {}
        const token = getToken()
        if (token) headers['Authorization'] = `Bearer ${token}`
        return headers
    }

const fetchSettings = async (withAuth = true) => {
  try {
    isLoading.value = true
    error.value = null

    // 👉 Nếu không cần token thì không gửi Authorization header
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
            const response = await API.post('api/settings', payload, { headers: getHeaders() })
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
