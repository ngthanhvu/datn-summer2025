// stores/coupon.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useCoupon } from '../composable/useCoupon'

export const useCouponStore = defineStore('coupon', () => {
    const coupons = ref([])
    const myCoupons = ref([])
    const loading = ref(false)
    const error = ref(null)

    const { getCoupons, getMyCoupons } = useCoupon()

    const fetchCoupons = async () => {
        loading.value = true
        error.value = null
        try {
            const data = await getCoupons()
            // Ensure data is always an array
            coupons.value = Array.isArray(data) ? data : (data?.data || data?.coupons || [])
        } catch (err) {
            error.value = err.message || 'Lỗi khi lấy mã giảm giá'
            coupons.value = [] // Reset to empty array on error
        } finally {
            loading.value = false
        }
    }

    const fetchMyCoupons = async () => {
        loading.value = true
        error.value = null
        try {
            const data = await getMyCoupons()
            // Ensure data is always an array
            myCoupons.value = Array.isArray(data) ? data : (data?.data || data?.coupons || [])
        } catch (err) {
            error.value = err.message || 'Lỗi khi lấy mã đã nhận'
            myCoupons.value = [] // Reset to empty array on error
        } finally {
            loading.value = false
        }
    }

    return {
        coupons,
        myCoupons,
        loading,
        error,
        fetchCoupons,
        fetchMyCoupons
    }
})
