<template>
    <div class="flex items-center justify-center p-4 sm:p-8 mt-12 mb-12">
        <main class="max-w-6xl w-full bg-white flex flex-col md:flex-row shadow-lg rounded-md overflow-hidden">
            <section class="flex-1 p-4 sm:p-6 md:p-10">
                <CartHeader :item-count="cartItems.length" />

                <!-- Skeleton Loading -->
                <div v-if="loading" class="space-y-4 animate-pulse">
                    <div v-for="i in 3" :key="i"
                        class="flex items-center justify-between border-b border-gray-300 pb-4">
                        <div class="flex items-center gap-4 w-full">
                            <div class="w-20 h-20 bg-gray-200 rounded"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty Cart -->
                <div v-else-if="cartItems.length === 0" class="text-center py-8">
                    <div class="text-gray-500 mb-4">
                        <i class="fas fa-shopping-cart text-4xl mb-3"></i>
                        <p class="text-lg">Giỏ hàng của bạn đang trống</p>
                    </div>
                    <a href="/product"
                        class="inline-flex items-center px-4 py-2 bg-[#81AACC] text-white rounded-md hover:bg-[#4a85b6] transition-colors">
                        <i class="fas fa-shopping-bag mr-2"></i> Mua sắm ngay
                    </a>
                </div>

                <!-- Cart Items -->
                <div v-else>
                    <!-- Stock Warning Banner -->
                    <div v-if="hasOverStockItems" class="mb-4 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                        <div class="flex items-center gap-2 text-orange-800">
                            <i class="fas fa-exclamation-triangle text-lg"></i>
                            <span class="font-semibold">Cảnh báo tồn kho</span>
                        </div>
                        <p class="text-orange-700 text-sm mt-1">
                            Một số sản phẩm trong giỏ hàng vượt quá số lượng tồn kho. Vui lòng kiểm tra và điều chỉnh số
                            lượng.
                        </p>

                        <!-- List of overstock items -->
                        <div class="mt-3 pt-3 border-t border-orange-200">
                            <p class="text-orange-700 text-xs font-semibold mb-2">Sản phẩm cần điều chỉnh:</p>
                            <div class="space-y-1">
                                <div v-for="item in overStockItems" :key="item.id"
                                    class="text-orange-700 text-xs flex items-center justify-between">
                                    <span>{{ item.variant?.product?.name }} ({{ item.variant?.size }}, {{
                                        item.variant?.color }})</span>
                                    <span class="font-semibold">
                                        {{ item.quantity }} > {{ getMaxAvailable(item) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto w-full">
                        <table class="w-full min-w-[800px]">
                            <tbody>
                                <CartItem v-for="item in cartItems" :key="item.id" :product="item"
                                    :quantity="item.quantity" :max-available="getMaxAvailable(item)"
                                    :external-error="itemErrors[item.id] || ''" @remove="handleRemove(item.id)"
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

            <CartSummary :item-count="cartItems.length" :subtotal="subtotal" :shipping="selectedShipping"
                :has-over-stock-items="hasOverStockItems" />
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import CartHeader from './CartHeader.vue'
import CartItem from './CartItem.vue'
import CartSummary from './CartSummary.vue'
import { useCart } from '../../composable/useCart'

const { cart, fetchCart, updateQuantity, removeFromCart, clearCart } = useCart()

const cartItems = computed(() => Array.isArray(cart.value) ? cart.value : [])
const itemErrors = ref({})
const loading = ref(true)

// Computed property để kiểm tra xem có sản phẩm nào vượt quá tồn kho không
const hasOverStockItems = computed(() => {
    return cartItems.value.some(item => {
        const maxAvailable = getMaxAvailable(item)
        return item.quantity > maxAvailable
    })
})

// Computed property để lấy danh sách sản phẩm vượt quá tồn kho
const overStockItems = computed(() => {
    return cartItems.value.filter(item => {
        const maxAvailable = getMaxAvailable(item)
        return item.quantity > maxAvailable
    })
})

const selectedShipping = ref({
    value: 'standard',
    price: 10
})

const subtotal = computed(() => {
    return cartItems.value.reduce((total, item) => total + (item.price * item.quantity), 0)
})

const getMaxAvailable = (item) => {
    const inv = item?.variant?.inventory?.quantity ?? 0
    const flashRemaining = item?.flash_sale?.remaining
    if (typeof flashRemaining === 'number') {
        return Math.min(inv, flashRemaining)
    }
    return inv
}

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
        if (!item || newQuantity <= 0) return

        const max = getMaxAvailable(item)
        if (newQuantity > max) {
            itemErrors.value[itemId] = `Số lượng tối đa còn lại là ${max}`
            await updateQuantity(itemId, max)
            return
        }

        await updateQuantity(itemId, newQuantity)
        itemErrors.value[itemId] = ''
    } catch (error) {
        const available = error?.response?.data?.available_quantity
        if (typeof available === 'number') {
            itemErrors.value[itemId] = `Số lượng tối đa còn lại là ${available}`
        } else {
            itemErrors.value[itemId] = 'Không thể cập nhật số lượng'
        }
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
        await clearCart()
    } catch (error) {
        console.error('Lỗi khi xóa giỏ hàng:', error)
    }
}

onMounted(async () => {
    try {
        await fetchCart();
    } finally {
        loading.value = false;
    }
});
</script>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>