import axios from 'axios'
import Swal from 'sweetalert2'

export const useCoupon = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl
    })

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

    API.interceptors.response.use(
        (response) => {
            return response
        },
        (error) => {
            console.error('API Error:', error.response?.data || error.message)
            return Promise.reject(error)
        }
    )

    const getCoupons = async () => {
        try {
            const response = await API.get('/api/coupons')

            if (response.data) {
                if (Array.isArray(response.data)) {
                    return response.data
                }
                if (response.data.coupons && Array.isArray(response.data.coupons)) {
                    return response.data.coupons
                }
                if (response.data.data && Array.isArray(response.data.data)) {
                    return response.data.data
                }
            }

            return []
        } catch (error) {
            console.error('Error getting coupons:', error)
            return []
        }
    }

    const getCouponById = async (id) => {
        try {
            const response = await API.get(`/api/coupons/${id}`)
            console.log('Coupon by ID response:', response.data)

            if (response.data) {
                if (response.data.coupon) {
                    return response.data.coupon
                }
                if (response.data.data) {
                    return response.data.data
                }
                return response.data
            }

            return null
        } catch (error) {
            console.error('Error getting coupon by ID:', error)
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
            console.log('Create coupon response:', response.data)
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
            console.log('Update coupon response:', response.data)
            return response.data
        } catch (error) {
            console.error('Error updating coupon:', error.response?.data || error)
            throw error
        }
    }

    const deleteCoupon = async (id) => {
        try {
            const response = await API.delete(`/api/coupons/${id}`)
            console.log('Delete coupon response:', response.data)
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
            console.log('Validate coupon response:', response.data)
            return response.data
        } catch (error) {
            console.error('Error validating coupon:', error)
            if (error.response?.data?.message) {
                throw new Error(error.response.data.message)
            } else if (error.response?.data?.error) {
                throw new Error(error.response.data.error)
            } else {
                throw new Error('Có lỗi xảy ra khi kiểm tra mã giảm giá')
            }
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