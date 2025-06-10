import axios from 'axios'
import { ref } from 'vue'
import { useCookie } from '#app'

const useCarts = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl
    const cart = ref([])
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
        console.log('Token:', token)
        return token
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

    const getCartEndpoint = () => {
        const token = getToken()
        return token ? 'cart' : 'guest-cart'
    }

    const fetchCart = async () => {
        try {
            isLoading.value = true
            const response = await axios.get(`${apiBaseUrl}api/${getCartEndpoint()}`, {
                headers: getHeaders(),
                withCredentials: true
            })
            cart.value = response.data
        } catch (err) {
            error.value = err.response?.data?.error || 'Có lỗi xảy ra'
        } finally {
            isLoading.value = false
        }
    }

    const addToCart = async (variantId, quantity = 1) => {
        try {
            isLoading.value = true
            const payload = { variant_id: variantId, quantity }

            if (quantity <= 0) {
                throw new Error('Số lượng phải lớn hơn 0')
            }

            const response = await axios.post(
                `${apiBaseUrl}api/${getCartEndpoint()}`,
                payload,
                {
                    headers: getHeaders(),
                    withCredentials: true
                }
            )
            await fetchCart()
            return response.data
        } catch (err) {
            if (err.response?.status === 401) {
                const tokenCookie = useCookie('token')
                tokenCookie.value = null
                localStorage.removeItem('token')

                return addToCart(variantId, quantity)
            }
            error.value = err.response?.data?.error || err.message || 'Không thể thêm vào giỏ hàng'
            throw error.value
        } finally {
            isLoading.value = false
        }
    }

    const updateQuantity = async (cartId, quantity) => {
        try {
            isLoading.value = true
            if (quantity <= 0) {
                throw new Error('Số lượng phải lớn hơn 0')
            }

            const currentItem = cart.value.find(item => item.id === cartId)
            if (!currentItem) {
                throw new Error('Không tìm thấy sản phẩm trong giỏ hàng')
            }

            if (!currentItem.variant) {
                throw new Error('Không thể xác định thông tin sản phẩm')
            }

            if (!currentItem.variant.inventory) {
                throw new Error('Không thể xác định số lượng tồn kho')
            }

            if (quantity > currentItem.variant.inventory.quantity) {
                throw new Error(`Số lượng vượt quá tồn kho. Chỉ còn ${currentItem.variant.inventory.quantity} sản phẩm.`)
            }

            await axios.put(
                `${apiBaseUrl}api/${getCartEndpoint()}/${cartId}`,
                { quantity },
                {
                    headers: getHeaders(),
                    withCredentials: true
                }
            )
            await fetchCart()
        } catch (err) {
            error.value = err.response?.data?.error || err.message || 'Không thể cập nhật số lượng'
            throw error.value
        } finally {
            isLoading.value = false
        }
    }

    const transferCartFromSessionToUser = async () => {
        const sessionId = localStorage.getItem('sessionId')
        const token = getToken()
        if (!sessionId || !token) return
        await axios.post(
            `${apiBaseUrl}api/cart/transfer-session-to-user`,
            {},
            {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-Session-Id': sessionId
                },
                withCredentials: true
            }
        )
        localStorage.removeItem('sessionId')
        await fetchCart()
    }

    const removeFromCart = async (cartId) => {
        try {
            isLoading.value = true
            await axios.delete(`${apiBaseUrl}api/${getCartEndpoint()}/${cartId}`, {
                headers: getHeaders(),
                withCredentials: true
            })
            await fetchCart()
        } catch (err) {
            error.value = err.response?.data?.error || 'Không thể xóa sản phẩm'
            throw error.value
        } finally {
            isLoading.value = false
        }
    }

    const generateSessionId = () => {
        const sessionId = Math.random().toString(36).substring(2)
        localStorage.setItem('sessionId', sessionId)
        return sessionId
    }

    const getUserId = () => {
        let user = null
        if (process.client) {
            user = useCookie('user').value
            if (!user && localStorage.getItem('user')) {
                user = localStorage.getItem('user')
            }
        } else {
            user = useCookie('user').value
        }
        try {
            if (typeof user === 'string') user = JSON.parse(decodeURIComponent(user))
        } catch { }
        return user?.id || null
    }

    const userId = getUserId()
    console.log('User ID:', userId)

    const increaseQuantity = async (cartId) => {
        try {
            const currentItem = cart.value.find(item => item.id === cartId)
            if (!currentItem) {
                throw new Error('Không tìm thấy sản phẩm trong giỏ hàng')
            }
            await updateQuantity(cartId, currentItem.quantity + 1)
        } catch (err) {
            error.value = err.message || 'Không thể tăng số lượng'
            throw error.value
        }
    }

    const decreaseQuantity = async (cartId) => {
        try {
            const currentItem = cart.value.find(item => item.id === cartId)
            if (!currentItem) {
                throw new Error('Không tìm thấy sản phẩm trong giỏ hàng')
            }
            if (currentItem.quantity > 1) {
                await updateQuantity(cartId, currentItem.quantity - 1)
            }
        } catch (err) {
            error.value = err.message || 'Không thể giảm số lượng'
            throw error.value
        }
    }

    return {
        cart,
        isLoading,
        error,
        fetchCart,
        addToCart,
        updateQuantity,
        removeFromCart,
        transferCartFromSessionToUser,
        getUserId,
        increaseQuantity,
        decreaseQuantity
    }
}

export default useCarts
export const useCart = useCarts

