// src/composables/useCoupon.js
import axios from 'axios'
import Cookies from 'js-cookie'

const API = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL,
    timeout: 10000
})

export const useCoupon = () => {
    const getTokenFromCookie = () => Cookies.get('token') || null

    const getCoupons = async () => {
        try {
            const response = await API.get('/api/coupons')
            const data = response.data

            if (Array.isArray(data)) return data
            if (Array.isArray(data?.coupons)) return data.coupons
            if (Array.isArray(data?.data)) return data.data

            return []
        } catch (error) {
            console.error('Error getting coupons:', error)
            return []
        }
    }

    const getCouponById = async (id) => {
        try {
            const response = await API.get(`/api/coupons/${id}`)
            const data = response.data
            return data?.coupon || data?.data || data || null
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
                    Accept: 'application/json'
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
                    Accept: 'application/json'
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
                    Accept: 'application/json'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error validating coupon:', error)
            const msg = error.response?.data?.message || error.response?.data?.error || 'Có lỗi xảy ra khi kiểm tra mã giảm giá'
            throw new Error(msg)
        }
    }

    const claimCoupon = async (couponId) => {
        try {
            const token = getTokenFromCookie()
            if (!token) throw new Error('Bạn chưa đăng nhập')

            const response = await API.post(`/api/coupons/${couponId}/claim`, {}, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    Accept: 'application/json'
                }
            })
            return response.data
        } catch (err) {
            console.error('Error claiming coupon:', err)
            throw err
        }
    }

    const getMyCoupons = async () => {
        try {
            const token = getTokenFromCookie()
            if (!token) throw new Error('Bạn chưa đăng nhập')

            const response = await API.get('/api/coupons/my-coupons', {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            })
            return response.data
        } catch (err) {
            console.error('Error getting claimed coupons:', err)
            throw err
        }
    }

    return {
        getCoupons,
        getCouponById,
        createCoupon,
        updateCoupon,
        deleteCoupon,
        validateCoupon,
        claimCoupon,
        getMyCoupons
    }
}
