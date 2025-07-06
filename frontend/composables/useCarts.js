import axios from 'axios'
import { ref } from 'vue'
import { useCookie } from '#app'

export const useCarts = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    if (!apiBaseUrl) {
        console.error('API Base URL is not configured')
        throw new Error('API configuration error')
    }

    const baseUrl = apiBaseUrl.endsWith('/') ? apiBaseUrl : `${apiBaseUrl}/`
    const cart = ref([])
    const isLoading = ref(false)
    const error = ref(null)

    // Tạo axios instance với timeout
    const API = axios.create({
        baseURL: baseUrl,
        timeout: 10000, // 10 giây timeout
        withCredentials: true
    })

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
            error.value = null
            const response = await API.get(`api/${getCartEndpoint()}`, {
                headers: getHeaders()
            })
            cart.value = response.data
            return response.data
        } catch (err) {
            console.error('Error fetching cart:', err)
            error.value = err.response?.data?.error || 'Có lỗi xảy ra khi tải giỏ hàng'
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const addToCart = async (variantId, quantity = 1) => {
        try {
            isLoading.value = true
            error.value = null
            const payload = { variant_id: variantId, quantity }

            if (quantity <= 0) {
                throw new Error('Số lượng phải lớn hơn 0')
            }

            const response = await API.post(
                `api/${getCartEndpoint()}`,
                payload,
                {
                    headers: getHeaders()
                }
            )
            await fetchCart()
            return response.data
        } catch (err) {
            console.error('Error adding to cart:', err)
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
            error.value = null
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

            await API.put(
                `api/${getCartEndpoint()}/${cartId}`,
                { quantity },
                {
                    headers: getHeaders()
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

        try {
            await API.post(
                `api/cart/transfer-session-to-user`,
                {},
                {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'X-Session-Id': sessionId
                    }
                }
            )
            localStorage.removeItem('sessionId')
            await fetchCart()
        } catch (error) {
            console.error('Transfer cart error:', error)
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

