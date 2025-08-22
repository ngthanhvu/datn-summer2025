import Cookies from 'js-cookie'
import api from '../utils/api'

const API = api;

const sampleCoupons = [
    {
        id: 1,
        code: 'DEVGANG',
        discount_type: 'percentage',
        discount_value: 5,
        description: 'Giảm 5% cho đơn hàng tối thiểu 200.000₫',
        minimum_amount: 200000,
        maximum_discount: 100000,
        valid_until: '2025-12-31'
    },
    {
        id: 2,
        code: 'FREESHIP',
        discount_type: 'shipping',
        discount_value: 0,
        description: 'Miễn phí vận chuyển cho đơn hàng tối thiểu 500.000₫',
        minimum_amount: 500000,
        valid_until: '2025-12-31'
    },
    {
        id: 3,
        code: 'GIAM50K',
        discount_type: 'fixed',
        discount_value: 50000,
        description: 'Giảm 50.000₫ cho đơn hàng tối thiểu 300.000₫',
        minimum_amount: 300000,
        valid_until: '2025-12-31'
    },
    {
        id: 4,
        code: 'GIAM30',
        discount_type: 'percentage',
        discount_value: 30,
        description: 'Giảm 30% cho đơn hàng tối thiểu 1.000.000₫',
        minimum_amount: 1000000,
        maximum_discount: 300000,
        valid_until: '2025-12-31'
    },
    {
        id: 5,
        code: 'GIAM40',
        discount_type: 'percentage',
        discount_value: 40,
        description: 'Giảm 40% cho đơn hàng tối thiểu 1.500.000₫',
        minimum_amount: 1500000,
        maximum_discount: 500000,
        valid_until: '2025-12-31'
    }
]

export const useCoupon = () => {
    const getTokenFromCookie = () => Cookies.get('token') || null

    const getCoupons = async () => {
        try {
            const response = await API.get('/api/coupons')
            const data = response.data

            if (Array.isArray(data)) return data
            if (Array.isArray(data?.coupons)) return data.coupons
            if (Array.isArray(data?.data)) return data.data

            // Fallback về coupon mẫu nếu API trả về dữ liệu không đúng định dạng
            console.warn('API returned invalid data format, using sample coupons')
            return sampleCoupons
        } catch (error) {
            console.error('Error getting coupons:', error)
            // Trả về coupon mẫu khi có lỗi
            return sampleCoupons
        }
    }

    const getNearestCoupons = async (limit = 3) => {
        try {
            const coupons = await getCoupons()
            // Lọc các coupon còn hiệu lực
            const validCoupons = coupons.filter(coupon => {
                if (coupon.valid_until) {
                    return new Date(coupon.valid_until) > new Date()
                }
                return true
            })

            // Sắp xếp theo thứ tự ưu tiên: percentage > fixed > shipping
            const sortedCoupons = validCoupons.sort((a, b) => {
                const priority = { percentage: 3, fixed: 2, shipping: 1 }
                return priority[b.discount_type] - priority[a.discount_type]
            })

            return sortedCoupons.slice(0, limit)
        } catch (error) {
            console.error('Error getting nearest coupons:', error)
            return sampleCoupons.slice(0, limit)
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
            if (!token) throw new Error('Vui lòng đăng nhập / đăng ký để nhận coupon')

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
        getNearestCoupons,
        getCouponById,
        createCoupon,
        updateCoupon,
        deleteCoupon,
        validateCoupon,
        claimCoupon,
        getMyCoupons
    }
}
