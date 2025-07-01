import { ref } from 'vue'
import axios from 'axios'
import { useAuth } from './useAuth'

export const useOrder = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl
    const { token, user } = useAuth()
    const orders = ref([])
    const currentOrder = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    API.interceptors.request.use((req) => {
        if (token.value) {
            req.headers.Authorization = `Bearer ${token.value}`
        }
        return req
    })

    const getAllOrders = async (params = {}) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.get('/api/orders', { params })
            orders.value = res.data
            return res.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            console.error('Get all orders error:', err.response?.data || err.message)
            throw err
        } finally {
            loading.value = false
        }
    }

    const getMyOrders = async (params = {}) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.get('/api/user/orders', { params })
            orders.value = res.data
            return res.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            console.error('Get my orders error:', err.response?.data || err.message)
            throw err
        } finally {
            loading.value = false
        }
    }

    const getOrder = async (id) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.get(`/api/orders/${id}`)
            currentOrder.value = res.data
            return res.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            console.error('Get order error:', err.response?.data || err.message)
            throw err
        } finally {
            loading.value = false
        }
    }

    const createOrder = async (orderData) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.post('/api/orders', orderData)
            return res.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            console.error('Create order error:', err.response?.data || err.message)
            if (router) {
                router.push({
                    path: '/status',
                    query: {
                        status: 'failed',
                        message: err.response?.data?.message || err.message,
                    }
                });
            }
            throw err
        } finally {
            loading.value = false
        }
    }

    const cancelOrder = async (id) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.post(`/api/orders/${id}/cancel`)
            return res.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            console.error('Cancel order error:', err.response?.data || err.message)
            throw err
        } finally {
            loading.value = false
        }
    }

    const updateOrderStatus = async (id, status, payment_status) => {
        loading.value = true
        error.value = null
        try {
            const payload = { status }
            if (payment_status !== undefined) payload.payment_status = payment_status
            const res = await API.put(`/api/orders/${id}/status`, payload)
            return res.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            console.error('Update order status error:', err.response?.data || err.message)
            throw err
        } finally {
            loading.value = false
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
            'pending': 'Chờ thanh toán',
            'paid': 'Đã thanh toán',
            'failed': 'Thanh toán thất bại',
            'refunded': 'Đã hoàn tiền',
            'canceled': 'Đã hủy'
        }
        return statuses[status] || status
    }

    const getPaymentMethod = (method) => {
        const methods = {
            'cod': 'Thanh toán khi nhận hàng',
            'vnpay': 'VNPay',
            'momo': 'MoMo',
            'paypal': 'PayPal'
        }
        return methods[method] || method
    }

    const formatPrice = (price) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price)
    }

    const getOrderByTrackingCode = async (trackingCode) => {
        loading.value = true;
        error.value = null;
        try {
            const res = await API.get(`/api/orders/track/${trackingCode}`);
            currentOrder.value = res.data;
            return res.data;
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            console.error('Get order by tracking code error:', err.response?.data || err.message);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        orders,
        currentOrder,
        loading,
        error,
        getAllOrders,
        getMyOrders,
        getOrder,
        createOrder,
        cancelOrder,
        updateOrderStatus,
        getOrderStatus,
        getPaymentStatus,
        getPaymentMethod,
        formatPrice,
        getOrderByTrackingCode
    }
} 