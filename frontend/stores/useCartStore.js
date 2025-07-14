import { defineStore } from 'pinia'
import axios from 'axios'

export const useCartStore = defineStore('cartStore', {
    state: () => ({
        cart: [],
        isLoadingCart: false,
        error: null
    }),
    actions: {
        async fetchCart() {
            this.isLoadingCart = true
            this.error = null
            try {
                const res = await axios.get('/api/cart')
                this.cart = res.data
            } catch (err) {
                this.error = err
            } finally {
                this.isLoadingCart = false
            }
        },
        async addToCart(item) {
            this.isLoadingCart = true
            this.error = null
            try {
                const res = await axios.post('/api/cart', item)
                this.cart = res.data
            } catch (err) {
                this.error = err
            } finally {
                this.isLoadingCart = false
            }
        },
        async removeFromCart(itemId) {
            this.isLoadingCart = true
            this.error = null
            try {
                const res = await axios.delete(`/api/cart/${itemId}`)
                this.cart = res.data
            } catch (err) {
                this.error = err
            } finally {
                this.isLoadingCart = false
            }
        },
        async updateCart(itemId, data) {
            this.isLoadingCart = true
            this.error = null
            try {
                const res = await axios.put(`/api/cart/${itemId}`, data)
                this.cart = res.data
            } catch (err) {
                this.error = err
            } finally {
                this.isLoadingCart = false
            }
        }
    }
}) 