import axios from 'axios'
import Swal from 'sweetalert2'

export const useCoupon = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    // Interceptor để thêm token vào header
    API.interceptors.request.use(
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

    const getCoupons = async () => {
        try {
            const response = await API.get('/api/coupons')
            return response.data?.coupons || [] // Sử dụng optional chaining và giá trị mặc định
        } catch (error) {
            console.error('Error getting coupons:', error)
            return [] // Trả về mảng rỗng nếu có lỗi
        }
    }

    const getCouponById = async (id) => {
        try {
            const response = await API.get(`/api/coupons/${id}`)
            return response.data.coupon
        } catch (error) {
            console.error('Error getting coupon:', error)
            throw error
        }
    }

    const createCoupon = async (couponData) => {
        try {
            const response = await API.post('/api/coupons', couponData, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error creating coupon:', error)
            throw error
        }
    }

    const updateCoupon = async (id, couponData) => {
        try {
            const response = await API.put(`/api/coupons/${id}`, couponData, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error updating coupon:', error.response?.data || error)
            throw error
        }
    }

    const deleteCoupon = async (id) => {
        try {
            const response = await API.delete(`/api/coupons/${id}`)
            return response.data
        } catch (error) {
            console.error('Error deleting coupon:', error)
            throw error
        }
    }

    const validateCoupon = async (code, totalAmount) => {
        try {
            const response = await API.post('/api/coupons/validate', {
                code,
                total_amount: totalAmount
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error validating coupon:', error)
            throw error
        }
    }

    return {
        getCoupons,
        getCouponById,
        createCoupon,
        updateCoupon,
        deleteCoupon,
        validateCoupon
    }
}