import { defineStore } from 'pinia'
import axios from 'axios'

export const useCouponStore = defineStore('couponStore', {
    state: () => ({
        coupons: [],
        isLoadingCoupons: false,
        error: null
    }),
    actions: {
        async fetchCoupons() {
            this.isLoadingCoupons = true
            this.error = null
            try {
                const res = await axios.get('/api/coupons')
                this.coupons = res.data
            } catch (err) {
                this.error = err
            } finally {
                this.isLoadingCoupons = false
            }
        },
        async applyCoupon(code) {
            this.isLoadingCoupons = true
            this.error = null
            try {
                const res = await axios.post('/api/coupons/apply', { code })
                // Xử lý kết quả áp dụng coupon nếu cần
                return res.data
            } catch (err) {
                this.error = err
                throw err
            } finally {
                this.isLoadingCoupons = false
            }
        },
        async removeCoupon(code) {
            this.isLoadingCoupons = true
            this.error = null
            try {
                const res = await axios.post('/api/coupons/remove', { code })
                // Xử lý kết quả xóa coupon nếu cần
                return res.data
            } catch (err) {
                this.error = err
                throw err
            } finally {
                this.isLoadingCoupons = false
            }
        }
    }
}) 