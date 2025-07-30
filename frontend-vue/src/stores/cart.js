// stores/cart.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import useCartApi from '../composable/useCart'

export const useCartStore = defineStore('cart', () => {
    const { cart, fetchCart, addToCart, removeFromCart, updateQuantity } = useCartApi()

    const totalQuantity = computed(() =>
        cart.value.reduce((total, item) => total + item.quantity, 0)
    )

    return {
        cart,
        fetchCart,
        addToCart,
        removeFromCart,
        updateQuantity,
        totalQuantity
    }
})
