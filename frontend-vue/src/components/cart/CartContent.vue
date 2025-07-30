<template>
    <div class="flex items-center justify-center p-4 sm:p-8 mt-12 mb-12">
        <main class="max-w-6xl w-full bg-white flex flex-col md:flex-row shadow-lg rounded-md overflow-hidden">
            <section class="flex-1 p-4 sm:p-6 md:p-10">
                <CartHeader :item-count="cartItems.length" />

                <div v-if="cartItems.length === 0" class="text-center py-8">
                    <div class="text-gray-500 mb-4">
                        <i class="fas fa-shopping-cart text-4xl mb-3"></i>
                        <p class="text-lg">Giỏ hàng của bạn đang trống</p>
                    </div>
                    <a href="/product"
                        class="inline-flex items-center px-4 py-2 bg-[#81AACC] text-white rounded-md hover:bg-[#4a85b6] transition-colors">
                        <i class="fas fa-shopping-bag mr-2"></i> Mua sắm ngay
                    </a>
                </div>

                <div v-else>
                    <div class="overflow-x-auto w-full">
                        <table class="w-full min-w-[800px]">
                            <tbody>
                                <CartItem v-for="item in cartItems" :key="item.id" :product="item"
                                    :quantity="item.quantity" @remove="handleRemove(item.id)"
                                    @decrease="handleDecrease(item.id)" @increase="handleIncrease(item.id)"
                                    @update:quantity="handleUpdateQuantity(item.id, $event)" />
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-4">
                        <router-link to="/san-pham"
                            class="inline-flex items-center text-sm text-[#81AACC] font-semibold select-none hover:text-[#4a85b6] transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i> Tiếp tục mua hàng
                        </router-link>
                        <button type="button"
                            class="inline-flex items-center text-sm text-red-500 font-semibold select-none hover:text-red-600 transition-colors cursor-pointer"
                            @click="handleClearCart">
                            <i class="fas fa-trash-alt mr-2"></i> Xóa toàn bộ giỏ hàng
                        </button>
                    </div>
                </div>
            </section>

            <CartSummary :item-count="cartItems.length" :subtotal="subtotal" :shipping="selectedShipping" />
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import CartHeader from './CartHeader.vue'
import CartItem from './CartItem.vue'
import CartSummary from './CartSummary.vue'
import { useCart } from '../../composable/useCart'

const { cart, fetchCart, updateQuantity, removeFromCart } = useCart()

const cartItems = computed(() => Array.isArray(cart.value) ? cart.value : [])

const selectedShipping = ref({
    value: 'standard',
    price: 10
})

const subtotal = computed(() => {
    return cartItems.value.reduce((total, item) => total + (item.price * item.quantity), 0)
})

const handleRemove = async (itemId) => {
    try {
        await removeFromCart(itemId)
    } catch (error) {
        console.error('Lỗi khi xóa sản phẩm:', error)
    }
}

const handleUpdateQuantity = async (itemId, newQuantity) => {
    try {
        const item = cartItems.value.find(i => i.id === itemId)
        if (!item || newQuantity <= 0 || !item.variant || !item.variant.inventory) return
        if (newQuantity > item.variant.inventory.quantity) return
        await updateQuantity(itemId, newQuantity)
    } catch (error) {
        console.error('Có lỗi xảy ra khi cập nhật số lượng:', error)
        await fetchCart()
    }
}

const handleIncrease = async (itemId) => {
    const item = cartItems.value.find(i => i.id === itemId)
    if (!item) return
    await handleUpdateQuantity(itemId, item.quantity + 1)
}

const handleDecrease = async (itemId) => {
    const item = cartItems.value.find(i => i.id === itemId)
    if (!item) return
    if (item.quantity > 1) {
        await handleUpdateQuantity(itemId, item.quantity - 1)
    }
}

const handleClearCart = async () => {
    try {
        for (const item of cartItems.value) {
            await removeFromCart(item.id)
        }
    } catch (error) {
        console.error('Lỗi khi xóa giỏ hàng:', error)
    }
}

onMounted(() => {
    if (!cartItems.value.length) {
        fetchCart()
    }
})
</script>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>