import { ref } from 'vue'
import api from '../utils/api'

export const useOrder = () => {
    const orders = ref([])
    const currentOrder = ref(null)
    const loading = ref(false)
    const error = ref(null)

    // Dùng instance API chung
    const API = api

    const getAllOrders = async (params = {}) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.get('/api/orders', { params })
            orders.value = Array.isArray(res.data.data) ? res.data.data : []
            return orders.value
        } catch (err) {
            error.value = err.response?.data?.message || err.message
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
            orders.value = Array.isArray(res.data.data) ? res.data.data : []
            return orders.value
        } catch (err) {
            error.value = err.response?.data?.message || err.message
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
            throw err
        } finally {
            loading.value = false
        }
    }

    const cancelOrder = async (id, reason = '') => {
        loading.value = true
        error.value = null
        try {
            const payload = reason ? { cancel_reason: reason } : {}
            const res = await API.post(`/api/orders/${id}/cancel`, payload)
            return res.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
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
            throw err
        } finally {
            loading.value = false
        }
    }

    const getOrderStatus = (status) => ({
        pending: 'Chờ xác nhận',
        processing: 'Đang xử lý',
        shipping: 'Đang giao hàng',
        completed: 'Hoàn thành',
        cancelled: 'Đã hủy'
    }[status] || status)

    const getPaymentStatus = (status) => ({
        pending: 'Chờ thanh toán',
        paid: 'Đã thanh toán',
        failed: 'Thanh toán thất bại',
        refunded: 'Đã hoàn tiền',
        canceled: 'Đã hủy'
    }[status] || status)

    const getPaymentMethod = (method) => ({
        cod: 'Thanh toán khi nhận hàng',
        vnpay: 'VNPay',
        momo: 'MoMo',
        paypal: 'PayPal'
    }[method] || method)

    const formatPrice = (price) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price)
    }

    const getOrderByTrackingCode = async (trackingCode) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.get(`/api/orders/track/${trackingCode}`)
            
            // Handle new response format: {success: true, order: {...}}
            if (res.data.success && res.data.order) {
                currentOrder.value = res.data.order
                return res.data.order
            } else if (res.data.order) {
                // Fallback for old format
                currentOrder.value = res.data.order
                return res.data.order
            } else {
                throw new Error(res.data.message || 'Không tìm thấy đơn hàng')
            }
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const reorderOrder = async (id) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.post(`/api/orders/${id}/reorder`)
            return res.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const requestReturn = async (id, return_reason) => {
        loading.value = true
        error.value = null
        try {
            const payload = return_reason ? { return_reason } : {}
            const res = await API.post(`/api/orders/${id}/return`, payload)
            return res.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const canRequestReturn = (order) => {
        if (!order) return false
        if (!['cancelled', 'completed'].includes(order.status)) return false
        if (order.return_requested_at) return false
        if (order.payment_method === 'cod') return false
        const completedAt = new Date(order.updated_at)
        const now = new Date()
        const diffDays = (now - completedAt) / (1000 * 60 * 60 * 24)
        return diffDays <= 3
    }

    const approveReturn = async (id) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.post(`/api/orders/${id}/return/approve`)
            return res.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

    const rejectReturn = async (id, reason) => {
        loading.value = true
        error.value = null
        try {
            const res = await API.post(`/api/orders/${id}/return/reject`, { reject_reason: reason })
            return res.data
        } catch (err) {
            error.value = err.response?.data?.message || err.message
            throw err
        } finally {
            loading.value = false
        }
    }

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
        getOrderByTrackingCode,
        reorderOrder,
        requestReturn,
        canRequestReturn,
        approveReturn,
        rejectReturn
    }
}
