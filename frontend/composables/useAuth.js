import { ref } from 'vue'
import axios from 'axios'

export const useAuth = () => {
    const token = useCookie('token')
    const userInfo = useCookie('user')
    const config = useRuntimeConfig()
    const user = ref(null)

    // Tạo API instance riêng
    const API = axios.create({
        baseURL: 'http://127.0.0.1:8000'
    })

    // Gắn Authorization nếu có token
    API.interceptors.request.use((req) => {
        if (token.value) {
            req.headers.Authorization = `Bearer ${token.value}`
        }
        return req
    })

    const login = async (credentials) => {
        try {
            const res = await API.post('/api/login', credentials)
            if (res.data.token) {
                token.value = res.data.token
                // Fetch user data after getting token
                await getUser()
                return true
            }
            return false
        } catch (err) {
            console.error('Login error:', err.response?.data || err.message)
            throw err
        }
    }

    const register = async (data) => {
        try {
            const res = await API.post('/api/register', data)
            if (res.data.token) {
                token.value = res.data.token
                if (res.data.user) {
                    userInfo.value = res.data.user
                    user.value = res.data.user
                }
                return true
            }
            return false
        } catch (err) {
            console.error('Register error:', err.response?.data || err.message)
            throw err
        }
    }

    const logout = () => {
        const tokenCookie = useCookie('token')
        const userInfoCookie = useCookie('user')

        tokenCookie.value = null
        userInfoCookie.value = null

        token.value = null
        userInfo.value = null
        user.value = null

        navigateTo('/login')
    }

    const getUser = async () => {
        if (!token.value) return
        try {
            const res = await API.get('/api/me')
            user.value = res.data
            userInfo.value = res.data
        } catch (err) {
            console.error('Get user error:', err.response?.data || err.message)
        }
    }

    // Initialize user from cookie if exists
    if (userInfo.value) {
        user.value = userInfo.value
    }

    return {
        user,
        token,
        login,
        register,
        logout,
        getUser
    }
}
