import axios from 'axios'
import Swal from 'sweetalert2'

export const useCoupon = () => {
    const config = useRuntimeConfig()
    const apiBaseUrl = config.public.apiBaseUrl

    const API = axios.create({
        baseURL: apiBaseUrl
    })

    const getTokenFromCookie = () => {
        const cookie = document.cookie
            .split('; ')
            .find(row => row.startsWith('token='))
        return cookie ? cookie.split('=')[1] : null
    }

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

    const claimCoupon = async (couponId) => {
        try {
            const token = getTokenFromCookie()
            if (!token) throw new Error('Bạn chưa đăng nhập')

            const response = await API.post(`/api/coupons/${couponId}/claim`, {}, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            console.log('Claim coupon response:', response.data)
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
            console.log('Claimed coupons response:', response.data)
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