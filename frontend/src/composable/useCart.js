import { ref } from 'vue'
import axios from 'axios'
import Cookies from 'js-cookie'

const API = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    timeout: 10000,
    withCredentials: true
})

// Singleton state
const cart = ref([])
const isLoading = ref(false)
const error = ref(null)

// Private helpers
const getToken = () => Cookies.get('token') || localStorage.getItem('token')

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
    const id = Math.random().toString(36).substring(2)
    localStorage.setItem('sessionId', id)
    return id
}

const fetchCart = async () => {
    try {
        isLoading.value = true
        error.value = null
        const res = await API.get(`api/${getCartEndpoint()}`, {
            headers: getHeaders()
        })
        cart.value = res.data || []
        return cart.value
    } catch (err) {
        error.value = err.response?.data?.error || 'Lỗi khi tải giỏ hàng'
        throw err
    } finally {
        isLoading.value = false
    }
}

const addToCart = async (variantId, quantity = 1, price = null) => {
    try {
        isLoading.value = true
        const payload = { variant_id: variantId, quantity }
        if (price !== null) payload.price = price

        await API.post(`api/${getCartEndpoint()}`, payload, {
            headers: getHeaders()
        })
        await fetchCart()
    } catch (err) {
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
        await API.put(`api/${getCartEndpoint()}/${cartId}`, { quantity }, {
            headers: getHeaders()
        })
        await fetchCart()
    } catch (err) {
        error.value = err.response?.data?.error || 'Không thể cập nhật số lượng'
        throw err
    } finally {
        isLoading.value = false
    }
}

const removeFromCart = async (cartId) => {
    try {
        isLoading.value = true
        await API.delete(`api/${getCartEndpoint()}/${cartId}`, {
            headers: getHeaders()
        })
        await fetchCart()
    } catch (err) {
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

export const useCart = () => {
    return {
        cart,
        isLoading,
        error,
        fetchCart,
        addToCart,
        updateQuantity,
        removeFromCart,
        increaseQuantity,
        decreaseQuantity
    }
}
