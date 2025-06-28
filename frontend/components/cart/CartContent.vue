<template>
    <div class="tw-flex tw-items-center tw-justify-center tw-p-4 sm:tw-p-8 tw-mt-12 tw-mb-12">
        <main
            class="tw-max-w-6xl tw-w-full tw-bg-white tw-flex tw-flex-col md:tw-flex-row tw-shadow-lg tw-rounded-md tw-overflow-hidden">
            <section class="tw-flex-1 tw-p-4 sm:tw-p-6 md:tw-p-10">
                <CartHeader :item-count="cartItems.length" />

                <div v-if="cartItems.length === 0" class="tw-text-center tw-py-8">
                    <div class="tw-text-gray-500 tw-mb-4">
                        <i class="fas fa-shopping-cart tw-text-4xl tw-mb-3"></i>
                        <p class="tw-text-lg">Giỏ hàng của bạn đang trống</p>
                    </div>
                    <NuxtLink to="/product"
                        class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-[#81AACC] tw-text-white tw-rounded-md hover:tw-bg-[#4a85b6] tw-transition-colors">
                        <i class="fas fa-shopping-bag tw-mr-2"></i> Mua sắm ngay
                    </NuxtLink>
                </div>

                <div v-else>
                    <div class="tw-overflow-x-auto tw-w-full">
                        <table class="tw-w-full tw-min-w-[800px]">
                            <tbody>
                                <CartItem v-for="item in cartItems" :key="item.id" :product="item"
                                    :quantity="item.quantity" @remove="handleRemove(item.id)"
                                    @decrease="handleDecrease(item.id)" @increase="handleIncrease(item.id)"
                                    @update:quantity="handleUpdateQuantity(item.id, $event)" />
                            </tbody>
                        </table>
                    </div>

                    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-items-center tw-mt-6 tw-gap-4">
                        <NuxtLink to="/product"
                            class="tw-inline-flex tw-items-center tw-text-sm tw-text-[#81AACC] tw-font-semibold tw-select-none hover:tw-text-[#4a85b6] tw-transition-colors">
                            <i class="fas fa-arrow-left tw-mr-2"></i> Tiếp tục mua hàng
                        </NuxtLink>
                        <button type="button"
                            class="tw-inline-flex tw-items-center tw-text-sm tw-text-red-500 tw-font-semibold tw-select-none hover:tw-text-red-600 tw-transition-colors"
                            @click="handleClearCart">
                            <i class="fas fa-trash-alt tw-mr-2"></i> Xóa toàn bộ giỏ hàng
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
import CartHeader from '~/components/cart/CartHeader.vue'
import CartItem from '~/components/cart/CartItem.vue'
import CartSummary from '~/components/cart/CartSummary.vue'
import { useCart } from '~/composables/useCarts'
const notyf = useNuxtApp().$notyf

const { cart, fetchCart, removeFromCart, updateQuantity, clearCart } = useCart()

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
        if (!item) {
            notyf.error('Không tìm thấy sản phẩm trong giỏ hàng')
            return
        }

        if (newQuantity <= 0) {
            notyf.error('Số lượng phải lớn hơn 0')
            return
        }

        if (!item.variant) {
            notyf.error('Không thể xác định thông tin sản phẩm')
            return
        }

        if (!item.variant.inventory) {
            notyf.error('Không thể xác định số lượng tồn kho')
            return
        }

        if (newQuantity > item.variant.inventory.quantity) {
            notyf.error(`Chỉ còn ${item.variant.inventory.quantity} sản phẩm trong kho`)
            return
        }

        await updateQuantity(itemId, newQuantity)
        notyf.success('Cập nhật số lượng thành công')
    } catch (error) {
        const errorMessage = error.response?.data?.error || error
        notyf.error(errorMessage || 'Có lỗi xảy ra khi cập nhật số lượng')

        await fetchCart()
    }
}

const handleIncrease = async (itemId) => {
    const item = cartItems.value.find(i => i.id === itemId)
    if (!item) return

    try {
        await handleUpdateQuantity(itemId, item.quantity + 1)
    } catch (error) {
        console.error('Lỗi khi tăng số lượng:', error)
    }
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
    fetchCart()
})
</script>

<style scoped>
/* Custom scrollbar for quantity input */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>