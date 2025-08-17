import axios from 'axios'
import Cookies from 'js-cookie'
import { ref } from 'vue'
import api from '../utils/api'

export function useCheckout() {
    // Lấy API base URL từ .env (chuẩn Vite)
    const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || ''

    // Sử dụng instance axios chung từ utility
    const API = api

    const isLoading = ref(false)
    const error = ref(null)

    const getToken = () => {
        // Ưu tiên lấy token từ cookie, fallback sang localStorage
        return Cookies.get('token') || localStorage.getItem('token') || null
    }

    const getHeaders = () => {
        const token = getToken()
        return token ? { Authorization: `Bearer ${token}` } : {}
    }

    const createOrder = async (orderData) => {
        try {
            isLoading.value = true
            error.value = null

            const response = await API.post('/api/orders', orderData, {
                headers: getHeaders()
            })

            return response.data
        } catch (err) {
            error.value =
                err.response?.data?.error || 'Có lỗi xảy ra khi tạo đơn hàng'
            throw error.value
        } finally {
            isLoading.value = false
        }
    }

    const applyCoupon = async (code, totalAmount) => {
        try {
            isLoading.value = true
            error.value = null

            const response = await API.post(
                '/api/coupons/validate',
                {
                    code,
                    total_amount: totalAmount
                },
                { headers: getHeaders() }
            )

            return response.data
        } catch (err) {
            error.value =
                err.response?.data?.error || 'Mã giảm giá không hợp lệ'
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

export default useCheckout
