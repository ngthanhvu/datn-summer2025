import axios from 'axios'
import { ref } from 'vue'

export const useCheckout = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    const isLoading = ref(false)
    const error = ref(null)

    const getToken = () => {
        let token = null
        if (process.client) {
            token = useCookie('token').value
            if (!token) token = localStorage.getItem('token')
        } else {
            token = useCookie('token').value
        }
        return token
    }

    const getHeaders = () => {
        const headers = {}
        const token = getToken()
        if (token) {
            headers['Authorization'] = `Bearer ${token}`
        }
        return headers
    }

    const createOrder = async (orderData) => {
        try {
            isLoading.value = true
            const response = await API.post('/api/orders', orderData, {
                headers: getHeaders()
            })
            return response.data
        } catch (err) {
            error.value = err.response?.data?.error || 'Có lỗi xảy ra khi tạo đơn hàng'
            throw error.value
        } finally {
            isLoading.value = false
        }
    }

    const applyCoupon = async (code, totalAmount) => {
        try {
            isLoading.value = true
            const response = await API.post('/api/coupons/validate', {
                code,
                total_amount: totalAmount
            }, {
                headers: getHeaders()
            })
            return response.data
        } catch (err) {
            error.value = err.response?.data?.error || 'Mã giảm giá không hợp lệ'
            throw error.value
        } finally {
            isLoading.value = false
        }
    }

    return {
        isLoading,
        error,
        createOrder,
        applyCoupon
    }
}