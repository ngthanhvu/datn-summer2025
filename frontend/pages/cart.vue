<template>
    <div class="tw-flex tw-items-center tw-justify-center tw-p-4 sm:tw-p-8">
        <main
            class="tw-max-w-6xl tw-w-full tw-bg-white tw-flex tw-flex-col md:tw-flex-row tw-shadow-lg tw-rounded-md tw-overflow-hidden">
            <section class="tw-flex-1 tw-p-4 sm:tw-p-6 md:tw-p-10">
                <CartHeader :item-count="cartItems.length" />

                <div class="tw-overflow-x-auto tw-w-full">
                    <table class="tw-w-full tw-min-w-[800px]">
                        <tbody>
                            <CartItem v-for="item in cartItems" :key="item.id" :product="item" :quantity="item.quantity"
                                @remove="removeItem(item.id)" @decrease="decreaseQuantity(item.id)"
                                @increase="increaseQuantity(item.id)"
                                @update:quantity="updateQuantity(item.id, $event)" />
                        </tbody>
                    </table>
                </div>

                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-items-center tw-mt-6 tw-gap-4">
                    <a href="#"
                        class="tw-inline-flex tw-items-center tw-text-sm tw-text-[#81AACC] tw-font-semibold tw-select-none hover:tw-text-[#4a85b6] tw-transition-colors">
                        <i class="fas fa-arrow-left tw-mr-2"></i> Tiếp tục mua hàng
                    </a>
                    <button type="button"
                        class="tw-inline-flex tw-items-center tw-text-sm tw-text-red-500 tw-font-semibold tw-select-none hover:tw-text-red-600 tw-transition-colors"
                        @click="clearCart">
                        <i class="fas fa-trash-alt tw-mr-2"></i> Xóa toàn bộ giỏ hàng
                    </button>
                </div>
            </section>

            <CartSummary :item-count="cartItems.length" :subtotal="subtotal" :shipping="selectedShipping"
                @update:shipping="updateShipping" @checkout="checkout" />
        </main>
    </div>
</template>

<script setup>
useHead({
    title: 'Giỏ hàng - DEVGANG',
    meta: [
        { name: 'description', content: 'Gio hàng - DEVGANG' },
    ],
})
import { ref, computed } from 'vue'
import CartHeader from '~/components/cart/CartHeader.vue'
import CartItem from '~/components/cart/CartItem.vue'
import CartSummary from '~/components/cart/CartSummary.vue'

const cartItems = ref([
    {
        id: 1,
        name: 'Iphone 6S',
        brand: 'Apple',
        price: 400,
        quantity: 1,
        image: 'https://images.unsplash.com/photo-1591337676887-a217a6970a8a?w=100&h=100&fit=crop'
    },
    {
        id: 2,
        name: 'Xiaomi Mi 20000mAh',
        brand: 'Xiaomi',
        price: 40,
        quantity: 1,
        image: 'https://images.unsplash.com/photo-1607083206968-13611e3d76db?w=100&h=100&fit=crop'
    },
    {
        id: 3,
        name: 'Airpods',
        brand: 'Apple',
        price: 150,
        quantity: 1,
        image: 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=100&h=100&fit=crop'
    }
])

const selectedShipping = ref({
    value: 'standard',
    price: 10
})

const subtotal = computed(() => {
    return cartItems.value.reduce((total, item) => total + (item.price * item.quantity), 0)
})

const removeItem = (id) => {
    cartItems.value = cartItems.value.filter(item => item.id !== id)
}

const decreaseQuantity = (id) => {
    const item = cartItems.value.find(item => item.id === id)
    if (item && item.quantity > 1) {
        item.quantity--
    }
}

const increaseQuantity = (id) => {
    const item = cartItems.value.find(item => item.id === id)
    if (item) {
        item.quantity++
    }
}

const updateQuantity = (id, value) => {
    const item = cartItems.value.find(item => item.id === id)
    if (item) {
        item.quantity = parseInt(value) || 1
    }
}

const clearCart = () => {
    cartItems.value = []
}

const updateShipping = (shipping) => {
    selectedShipping.value = shipping
}

const checkout = () => {
    // Implement checkout logic
    console.log('Checkout', {
        items: cartItems.value,
        shipping: selectedShipping.value,
        total: subtotal.value + selectedShipping.value.price
    })
}
</script>

<style>
/* Custom scrollbar for quantity input */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>