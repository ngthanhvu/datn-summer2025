import axios from 'axios'
import Swal from 'sweetalert2'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    }
})

api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    (error) => {
        return Promise.reject(error)
    }
)

api.interceptors.response.use(
    (response) => {
        return response
    },
    async (error) => {
        if (error.response?.status === 401) {
            const isAuthEndpoint = error.config?.url?.includes('/login') ||
                error.config?.url?.includes('/register') ||
                error.config?.url?.includes('/forgot-password') ||
                error.config?.url?.includes('/reset-password')

            if (!isAuthEndpoint) {
                localStorage.removeItem('token')
                localStorage.removeItem('user')

                document.cookie = 'token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'
                document.cookie = 'user=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;'

                // alert('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.')
                await Swal.fire({
                    icon: 'warning',
                    title: 'Phiên đăng nhập hàng tại hết hạn.',
                    text: 'Vui lòng đăng nhập lâi.',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '/login'
                    }
                })


            }
        }
        return Promise.reject(error)
    }
)

export default api 