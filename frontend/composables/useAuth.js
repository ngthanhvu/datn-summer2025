import { ref } from 'vue'
import axios from 'axios'
import Swal from 'sweetalert2'

export const useAuth = () => {
    const token = useCookie('token')
    const userInfo = useCookie('user')
    const config = useRuntimeConfig()
    const user = ref(null)
    const isAuthenticated = ref(false)
    const isAdmin = ref(false)
    const apiBaseUrl = config.public.apiBaseUrl
    
    const API = axios.create({
        baseURL: apiBaseUrl
    })

    API.interceptors.request.use((req) => {
        if (token.value) {
            req.headers.Authorization = `Bearer ${token.value}`
        }
        return req
    })

    const googleLogin = async () => {
        try {
            const res = await API.get('/api/google')
            if (res.data.url) {
                window.location.href = res.data.url
            } else {
                throw new Error('Failed to get Google login URL')
            }
        } catch (err) {
            console.error('Google login error:', err.response?.data || err.message)
            throw err
        }
    }

    const handleGoogleCallback = async () => {
        try {
            const tokenFromQuery = useRoute().query.token
            const userFromQuery = useRoute().query.user
            const error = useRoute().query.error

            if (error) {
                throw new Error(error)
            }

            if (!tokenFromQuery || !userFromQuery) {
                throw new Error('Missing token or user from Google callback')
            }

            token.value = tokenFromQuery
            userInfo.value = JSON.parse(decodeURIComponent(userFromQuery))
            user.value = userInfo.value
            isAuthenticated.value = true
            isAdmin.value = user.value?.role === 'admin'

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            })

            Toast.fire({
                icon: 'success',
                title: 'Đăng nhập thành công!'
            })

            navigateTo('/')
            return true
        } catch (err) {
            console.error('Google callback error:', err.response?.data || err.message)
            throw err
        }
    }

    const facebookLogin = async () => {
        Swal.fire({
            title: "Tính năng đang phát triển",
            icon: "info",
            confirmButtonText: "OK"
        })
    }

    const checkAuth = async () => {
        if (token.value) {
            try {
                await getUser()
                isAuthenticated.value = true
                return true
            } catch (error) {
                logout()
                return false
            }
        }
        return false
    }

    const checkAdmin = async () => {
        if (!isAuthenticated.value) {
            await checkAuth()
        }

        if (user.value?.role === 'admin') {
            isAdmin.value = true
            return true
        }

        isAdmin.value = false
        return false
    }

    const login = async (credentials) => {
        try {
            const res = await API.post('/api/login', credentials)
            if (res.data.token) {
                token.value = res.data.token
                await getUser()
                isAuthenticated.value = true
                isAdmin.value = user.value?.role === 'admin'
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
                    isAuthenticated.value = true
                    isAdmin.value = user.value?.role === 'admin'
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
        isAuthenticated.value = false
        isAdmin.value = false

        navigateTo('/login')
    }

    const getUser = async () => {
        if (!token.value) return
        try {
            const res = await API.get('/api/me')
            user.value = res.data
            userInfo.value = res.data
            isAuthenticated.value = true
            isAdmin.value = user.value?.role === 'admin'
        } catch (err) {
            console.error('Get user error:', err.response?.data || err.message)
            logout()
        }
    }

    const getListUser = async () => {
        try {
            const res = await API.get('/api/admin/user')
            return res.data
        } catch (err) {
            console.error('Get list user error:', err.response?.data || err.message)
            throw err
        }
    }

    if (userInfo.value) {
        user.value = userInfo.value
        isAuthenticated.value = true
        isAdmin.value = user.value?.role === 'admin'
    }

    return {
        user,
        token,
        login,
        register,
        logout,
        getUser,
        isAuthenticated,
        isAdmin,
        checkAuth,
        checkAdmin,
        googleLogin,
        handleGoogleCallback,
        facebookLogin,
        getListUser
    }
}
