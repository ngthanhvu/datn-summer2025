import { ref } from 'vue'
import axios from 'axios'
import Cookies from 'js-cookie'
import { useRouter } from 'vue-router'

const token = ref(Cookies.get('token') || null)
const userInfo = ref(Cookies.get('user') ? JSON.parse(Cookies.get('user')) : null)
const user = ref(userInfo.value || null)
const isAuthenticated = ref(!!user.value)
const isAdmin = ref(user.value?.role === 'admin')
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL // cấu hình qua .env

const API = axios.create({
    baseURL: apiBaseUrl,
})

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
            Cookies.set('token', token.value, { expires: 1 })
            await getUser()
            if (user.value) {
                localStorage.setItem("user", JSON.stringify(user.value))
            }
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
            Cookies.set('token', token.value, { expires: 1 })
            if (res.data.user) {
                userInfo.value = res.data.user
                Cookies.set('user', JSON.stringify(userInfo.value), { expires: 1 })
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
    Cookies.remove('token')
    Cookies.remove('user')
    token.value = null
    userInfo.value = null
    user.value = null
    isAuthenticated.value = false
    isAdmin.value = false
    // Không chuyển hướng ở đây
}

const getUser = async () => {
    if (!token.value) return
    try {
        const res = await API.get('/api/me')
        user.value = res.data
        userInfo.value = res.data
        Cookies.set('user', JSON.stringify(res.data), { expires: 1 })
        isAuthenticated.value = true
        isAdmin.value = user.value?.role === 'admin'
    } catch (err) {
        console.error('Get user error:', err.response?.data || err.message)
        logout()
    }
}

const checkAuth = async () => {
    if (token.value) {
        try {
            await getUser()
            return true
        } catch {
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
    isAdmin.value = user.value?.role === 'admin'
    return isAdmin.value
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

const forgotPassword = async (email) => {
    try {
        const res = await API.post('/api/forgot-password', { email })
        return res.data
    } catch (err) {
        console.error('Forgot password error:', err.response?.data || err.message)
        throw err
    }
}

const resetPassword = async (email, otp, password, password_confirmation) => {
    try {
        const res = await API.post('/api/reset-password', {
            email,
            otp,
            password,
            password_confirmation
        })
        return res.data
    } catch (err) {
        console.error('Reset password error:', err.response?.data || err.message)
        throw err
    }
}

const updateUserProfile = async (formData) => {
    try {
        const res = await API.post('/api/update-profile', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        })
        if (res.data) {
            await getUser()
            return res.data
        }
    } catch (err) {
        console.error('Update profile error:', err.response?.data || err.message)
        throw err
    }
}

const resetPasswordProfile = async (currentPassword, newPassword, newPasswordConfirmation) => {
    try {
        const res = await API.post('/api/reset-password-profile', {
            current_password: currentPassword,
            password: newPassword,
            password_confirmation: newPasswordConfirmation
        })
        return res.data
    } catch (err) {
        console.error('Reset password profile error:', err.response?.data || err.message)
        throw err
    }
}

const updateUser = async (userData) => {
    try {
        const res = await API.put(`/api/admin/user/${userData.id}`, userData)
        return res.data
    } catch (err) {
        console.error('Update user error:', err.response?.data || err.message)
        throw err
    }
}

const getToken = () => token.value

export const useAuth = () => {
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
        getListUser,
        forgotPassword,
        resetPassword,
        updateUserProfile,
        resetPasswordProfile,
        updateUser,
        getToken
    }
}
