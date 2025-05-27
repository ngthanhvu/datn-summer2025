import { ref } from 'vue'
import axios from 'axios'

export const useAuth = () => {
    const token = useCookie('token')
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

    const login = async (email, password) => {
        try {
            const res = await API.post('/api/login', { email, password })
            token.value = res.data.token
            await getUser()
            return true
        } catch (err) {
            console.error('Login error:', err.response?.data || err.message)
            return false
        }
    }

    const register = async (data) => {
        try {
            const res = await API.post('/api/register', data)
            token.value = res.data.token
            await getUser()
            return true
        } catch (err) {
            console.log('Register error:', err.response?.data || err.message)
            return false
        }
    }

    const logout = () => {
        token.value = null
        user.value = null
    }

    const getUser = async () => {
        if (!token.value) return
        try {
            const res = await API.get('/api/user')
            user.value = res.data
        } catch (err) {
            console.error('Get user error:', err.response?.data || err.message)
        }
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
