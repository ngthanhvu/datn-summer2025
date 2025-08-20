import { ref } from 'vue'
import Cookies from 'js-cookie'
import { useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import api from '../utils/api'

const token = ref(Cookies.get('token') || localStorage.getItem('token') || null)
const userInfo = ref(Cookies.get('user') ? JSON.parse(Cookies.get('user')) : null)
const user = ref(userInfo.value || null)
const isAuthenticated = ref(!!user.value)
const isAdmin = ref(user.value?.role === 'admin')

const syncStorage = () => {
    const cookieToken = Cookies.get('token')
    const localToken = localStorage.getItem('token')
    const cookieUser = Cookies.get('user')
    const localUser = localStorage.getItem('user')

    if (localToken && !cookieToken) {
        Cookies.set('token', localToken, { expires: 1 })
        token.value = localToken
    }
    else if (cookieToken && !localToken) {
        localStorage.setItem('token', cookieToken)
        token.value = cookieToken
    }

    if (localUser && !cookieUser) {
        Cookies.set('user', localUser, { expires: 1 })
        try {
            userInfo.value = JSON.parse(localUser)
            user.value = userInfo.value
        } catch (e) {
            console.error('Error parsing user from localStorage:', e)
        }
    }
    else if (cookieUser && !localUser) {
        localStorage.setItem('user', cookieUser)
        try {
            userInfo.value = JSON.parse(cookieUser)
            user.value = userInfo.value
        } catch (e) {
            console.error('Error parsing user from Cookies:', e)
        }
    }

    isAuthenticated.value = !!token.value
    isAdmin.value = user.value?.role === 'admin'
}

syncStorage()

const API = api

const login = async (credentials) => {
    try {
        const res = await API.post('/api/login', credentials)
        if (res.data.token) {
            token.value = res.data.token
            Cookies.set('token', token.value, { expires: 1 })
            localStorage.setItem('token', token.value)
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
            localStorage.setItem('token', token.value)
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

const logout = (redirectToLogin = false) => {
    Cookies.remove('token')
    Cookies.remove('user')
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    token.value = null
    userInfo.value = null
    user.value = null
    isAuthenticated.value = false
    isAdmin.value = false

    if (redirectToLogin) {
        const router = useRouter()
        router.push('/login')
    }
}

const forceLogout = () => {
    Cookies.remove('token')
    Cookies.remove('user')
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    token.value = null
    userInfo.value = null
    user.value = null
    isAuthenticated.value = false
    isAdmin.value = false
}

const getUser = async () => {
    if (!token.value) return
    try {
        const res = await API.get('/api/me')
        user.value = res.data
        userInfo.value = res.data
        Cookies.set('user', JSON.stringify(res.data), { expires: 1 })
        localStorage.setItem('user', JSON.stringify(res.data))
        isAuthenticated.value = true
        isAdmin.value = user.value?.role === 'admin'
    } catch (err) {
        console.error('Get user error:', err.response?.data || err.message)
        if (err.response?.status === 401 && token.value) {
            forceLogout()
        }
    }
}

const initializeAuth = async () => {
    if (token.value) {
        try {
            await getUser()
        } catch (error) {
            console.log('Token không hợp lệ, đăng xuất...')
            forceLogout()
        }
    }
}

const checkAuth = async () => {
    if (token.value) {
        try {
            await getUser()
            return true
        } catch {
            forceLogout()
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

const updateCustomerStatus = async (id, status) => {
    try {
        const res = await API.put(`/api/admin/user/${id}`, { status })
        return res.data
    } catch (err) {
        console.error('Update customer status error:', err.response?.data || err.message)
        throw err
    }
}

const updateUserByAdmin = async (data) => {
    try {
        const res = await API.put(`/api/admin/user/${data.id}`, data)
        return res.data
    } catch (err) {
        console.error('Update user by admin error:', err.response?.data || err.message)
        throw err
    }
}

const createUserByAdmin = async (data) => {
    try {
        const res = await API.post('/api/admin/user', data)
        return res.data
    } catch (err) {
        console.error('Create user by admin error:', err.response?.data || err.message)
        throw err
    }
}

const deleteUser = async (id) => {
    try {
        const res = await API.delete(`/api/admin/user/${id}`)
        return res.data
    } catch (err) {
        console.error('Delete user error:', err.response?.data || err.message)
        throw err
    }
}

const googleLogin = async () => {
    try {
        const res = await API.get('/api/google')
        if (res.data.url) {
            window.location.href = res.data.url
        } else {
            throw new Error('Không lấy được Google login URL')
        }
    } catch (err) {
        console.error('Google login error:', err.response?.data || err.message)
        throw err
    }
}

const handleGoogleCallback = async (tokenFromQuery, userFromQuery, error) => {
    try {
        if (error) {
            throw new Error(error)
        }
        if (!tokenFromQuery || !userFromQuery) {
            throw new Error('Thiếu token hoặc user từ Google callback')
        }

        token.value = tokenFromQuery
        Cookies.set('token', token.value, { expires: 1 })
        localStorage.setItem('token', token.value)

        const parsedUser = JSON.parse(decodeURIComponent(userFromQuery))
        userInfo.value = parsedUser
        user.value = parsedUser
        Cookies.set('user', JSON.stringify(parsedUser), { expires: 1 })
        localStorage.setItem('user', JSON.stringify(parsedUser))

        isAuthenticated.value = true
        isAdmin.value = user.value?.role === 'admin'

        Swal.fire({
            toast: true,
            icon: 'success',
            title: 'Đăng nhập thành công!',
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        })

        return true
    } catch (err) {
        console.error('Google callback error:', err.response?.data || err.message)
        throw err
    }
}

const getToken = () => token.value

// Initialize auth after all functions are declared
initializeAuth()

export const useAuth = () => {
    return {
        user,
        token,
        login,
        register,
        logout,
        forceLogout,
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
        getToken,
        updateUserByAdmin,
        createUserByAdmin,
        updateCustomerStatus,
        deleteUser,
        googleLogin,
        handleGoogleCallback,
        syncStorage,
        initializeAuth
    }
}
