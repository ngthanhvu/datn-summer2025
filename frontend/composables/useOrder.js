import { ref } from 'vue'
import axios from 'axios'
import { useAuth } from './useAuth'

export const useOrder = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl
    const { token } = useAuth()
    const orders = ref([])
    const currentOrder = ref(null)

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    API.interceptors.request.use((req) => {
        if (token.value) {
            req.headers.Authorization = `Bearer ${token.value}`
        }
        return req
    })

    const getOrders = async () => {
        try {
            const res = await API.get('/api/orders')
            orders.value = res.data
            return res.data
        } catch (err) {
            console.error('Get orders error:', err.response?.data || err.message)
            throw err
        }
    }

    const getOrder = async (id) => {
        try {
            const res = await API.get(`/api/orders/${id}`)
            currentOrder.value = res.data
            return res.data
        } catch (err) {
            console.error('Get order error:', err.response?.data || err.message)
            throw err
        }
    }

    const createOrder = async (orderData) => {
        try {
            const res = await API.post('/api/orders', orderData)
            return res.data
        } catch (err) {
            console.error('Create order error:', err.response?.data || err.message)
            throw err
        }
    }

    const cancelOrder = async (id) => {
        try {
            const res = await API.post(`/api/orders/${id}/cancel`)
            return res.data
        } catch (err) {
            console.error('Cancel order error:', err.response?.data || err.message)
            throw err
        }
    }

    const getOrderStatus = (status) => {
        const statuses = {
            'pending': 'Chờ xác nhận',
            'processing': 'Đang xử lý',
            'shipping': 'Đang giao hàng',
            'completed': 'Hoàn thành',
            'cancelled': 'Đã hủy'
        }
        return statuses[status] || status
    }

    const getPaymentStatus = (status) => {
        const statuses = {
            'unpaid': 'Chưa thanh toán',
            'paid': 'Đã thanh toán',
            'failed': 'Thanh toán thất bại',
            'refunded': 'Đã hoàn tiền'
        }
        return statuses[status] || status
    }

    return {
        orders,
        currentOrder,
        getOrders,
        getOrder,
        createOrder,
        cancelOrder,
        getOrderStatus,
        getPaymentStatus
    }
} 