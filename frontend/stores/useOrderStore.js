import { defineStore } from 'pinia'
import axios from 'axios'

export const useOrderStore = defineStore('orderStore', {
    state: () => ({
        orders: [],
        isLoadingOrders: false,
        error: null
    }),
    actions: {
        async fetchOrders() {
            this.isLoadingOrders = true
            this.error = null
            try {
                const res = await axios.get('/api/orders')
                this.orders = Array.isArray(res.data.data) ? res.data.data : []
            } catch (err) {
                this.error = err
            } finally {
                this.isLoadingOrders = false
            }
        },
        async createOrder(orderData) {
            this.isLoadingOrders = true
            this.error = null
            try {
                const res = await axios.post('/api/orders', orderData)
                this.orders.push(res.data)
            } catch (err) {
                this.error = err
            } finally {
                this.isLoadingOrders = false
            }
        },
        async updateOrder(orderId, data) {
            this.isLoadingOrders = true
            this.error = null
            try {
                const res = await axios.put(`/api/orders/${orderId}`, data)
                const idx = this.orders.findIndex(o => o.id === orderId)
                if (idx !== -1) this.orders[idx] = res.data
            } catch (err) {
                this.error = err
            } finally {
                this.isLoadingOrders = false
            }
        }
    }
}) 