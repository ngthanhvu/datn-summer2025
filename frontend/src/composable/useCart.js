import { ref } from 'vue'
import axios from 'axios'
import Cookies from 'js-cookie'
import api from '../utils/api'

// Sử dụng instance axios chung từ utility
const API = api

// Singleton state
const cart = ref([])
const isLoading = ref(false)
const error = ref(null)

// Private helpers
const getToken = () => {
    const token = Cookies.get('token') || localStorage.getItem('token');
    if (!token || token === 'null' || token === 'undefined' || token === '') return null;
    return token;
}

const getHeaders = () => {
    const headers = {}
    const token = getToken()
    if (token) {
        headers['Authorization'] = `Bearer ${token}`
    } else {
        const sessionId = localStorage.getItem('sessionId') || generateSessionId()
        headers['X-Session-Id'] = sessionId
    }
    return headers
}

const getCartEndpoint = () => getToken() ? 'cart' : 'guest-cart'

const generateSessionId = () => {
    const id = Math.random().toString(36).substring(2) + Date.now().toString(36)
    localStorage.setItem('sessionId', id)
    return id
}

const syncSessionCartToUser = async () => {
    try {
        const token = getToken()
        const sessionId = localStorage.getItem('sessionId')
        if (!token || !sessionId) return

        // Gọi API để chuyển toàn bộ giỏ hàng từ session sang user sau khi đăng nhập
        await API.post('api/cart/transfer-session-to-user', {}, {
            headers: {
                Authorization: `Bearer ${token}`,
                'X-Session-Id': sessionId
            }
        })
        // Xóa sessionId để tránh gọi lại nhiều lần
        localStorage.removeItem('sessionId')
        return true
    } catch (err) {
        console.warn('Failed to sync session cart to user:', err)
        // Bỏ qua lỗi đồng bộ, không chặn luồng người dùng
        return false
    }
}

const fetchCart = async () => {
    try {
        isLoading.value = true
        error.value = null

        // Nếu đã đăng nhập và còn session giỏ hàng cũ thì hợp nhất trước
        if (getToken()) {
            await syncSessionCartToUser()
        }

        const res = await API.get(`api/${getCartEndpoint()}`, {
            headers: getHeaders()
        })
        cart.value = res.data || []
        return cart.value
    } catch (err) {
        console.error('Error fetching cart:', err)
        error.value = err.response?.data?.error || 'Lỗi khi tải giỏ hàng'
        cart.value = []
        return []
    } finally {
        isLoading.value = false
    }
}

const addToCart = async (variantId, quantity = 1, price = null) => {
    try {
        isLoading.value = true
        error.value = null

        const payload = { variant_id: variantId, quantity }
        if (price !== null) payload.price = price

        const res = await API.post(`api/${getCartEndpoint()}`, payload, {
            headers: getHeaders()
        })

        await fetchCart()

        // Trả về item vừa thêm (để caller có thể hiển thị remaining flash sale nếu có)
        const added = cart.value
            .filter(i => i.variant_id === variantId)
            .sort((a, b) => b.id - a.id)[0]
        return added || res.data
    } catch (err) {
        console.error('Error adding to cart:', err)
        error.value = err.response?.data?.error || 'Không thể thêm vào giỏ hàng'
        throw err
    } finally {
        isLoading.value = false
    }
}

const updateQuantity = async (cartId, quantity) => {
    try {
        if (quantity <= 0) throw new Error('Số lượng phải lớn hơn 0')
        isLoading.value = true
        error.value = null

        await API.put(`api/${getCartEndpoint()}/${cartId}`, { quantity }, {
            headers: getHeaders()
        })
        await fetchCart()
    } catch (err) {
        console.error('Error updating quantity:', err)
        error.value = err.response?.data?.error || 'Không thể cập nhật số lượng'
        throw err
    } finally {
        isLoading.value = false
    }
}

const removeFromCart = async (cartId) => {
    try {
        isLoading.value = true
        error.value = null

        await API.delete(`api/${getCartEndpoint()}/${cartId}`, {
            headers: getHeaders()
        })
        await fetchCart()
    } catch (err) {
        console.error('Error removing from cart:', err)
        error.value = err.response?.data?.error || 'Không thể xoá sản phẩm'
        throw err
    } finally {
        isLoading.value = false
    }
}

const increaseQuantity = async (cartId) => {
    const item = cart.value.find(i => i.id === cartId)
    if (item) {
        await updateQuantity(cartId, item.quantity + 1)
    }
}

const decreaseQuantity = async (cartId) => {
    const item = cart.value.find(i => i.id === cartId)
    if (item && item.quantity > 1) {
        await updateQuantity(cartId, item.quantity - 1)
    }
}

const clearCart = async () => {
    try {
        isLoading.value = true
        error.value = null

        // Xóa từng item một cách tuần tự
        const cartItems = [...cart.value]
        for (const item of cartItems) {
            await removeFromCart(item.id)
        }

        cart.value = []
    } catch (err) {
        console.error('Error clearing cart:', err)
        error.value = 'Không thể xóa giỏ hàng'
        throw err
    } finally {
        isLoading.value = false
    }
}

// Thêm method cleanupOldCartItems
const cleanupOldCartItems = async () => {
    try {
        // Kiểm tra xem user đã đăng nhập chưa
        const token = getToken()
        if (!token) {
            console.log('User not logged in, skipping cleanup old cart items')
            return { message: 'User not logged in' }
        }

        isLoading.value = true
        error.value = null

        const res = await API.post('api/cart/cleanup-old-items', {}, {
            headers: getHeaders()
        })

        return res.data
    } catch (err) {
        console.error('Error cleaning up old cart items:', err)
        error.value = 'Không thể dọn dẹp giỏ hàng cũ'
        throw err
    } finally {
        isLoading.value = false
    }
}

// Thêm method clearCartAfterPayment
const clearCartAfterPayment = async () => {
    try {
        // Kiểm tra xem user đã đăng nhập chưa
        const token = getToken()
        if (!token) {
            console.log('User not logged in, skipping clear cart after payment')
            return { message: 'User not logged in' }
        }

        isLoading.value = true
        error.value = null

        const res = await API.post('api/orders/clear-cart-after-payment', {}, {
            headers: getHeaders()
        })

        cart.value = []
        console.log('Cart cleared after payment:', res.data)
        return res.data
    } catch (err) {
        console.error('Error clearing cart after payment:', err)
        error.value = 'Không thể xóa giỏ hàng sau thanh toán'
        throw err
    } finally {
        isLoading.value = false
    }
}

export const useCart = () => {
    return {
        cart,
        isLoading,
        error,
        syncSessionCartToUser,
        fetchCart,
        addToCart,
        updateQuantity,
        removeFromCart,
        increaseQuantity,
        decreaseQuantity,
        clearCart,
        cleanupOldCartItems,
        clearCartAfterPayment
    }
}