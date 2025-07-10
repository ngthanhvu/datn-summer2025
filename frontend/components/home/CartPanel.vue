<template>
    <div class="cart-panel" :class="{ 'cart-panel-open': isOpen }">
        <div class="cart-panel-content">
            <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                <h6 class="tw-font-bold tw-m-0">Giỏ hàng ({{ cartStore.cart?.length || 0 }})</h6>
                <button @click="$emit('close')" class="tw-text-gray-500 hover:tw-text-gray-700">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <!-- Cart Items -->
            <div class="tw-space-y-4 tw-max-h-[calc(100vh-200px)] tw-overflow-y-auto">
                <div v-if="!cartStore.cart?.length" class="tw-text-center tw-py-8">
                    <div class="tw-text-gray-500 tw-mb-4">
                        <i class="bi bi-cart tw-text-4xl tw-block tw-mb-3"></i>
                        <p class="tw-text-lg">Giỏ hàng của bạn đang trống</p>
                    </div>
                    <NuxtLink to="/product"
                        class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-[#81AACC] tw-text-white tw-rounded-md hover:tw-bg-[#4a85b6] tw-transition-colors">
                        <i class="bi bi-bag tw-mr-2"></i> Mua sắm ngay
                    </NuxtLink>
                </div>
                <div v-else v-for="item in cartStore.cart" :key="item.id" class="tw-flex tw-gap-4 tw-pb-4 tw-border-b">
                    <img :src="getImageUrl(item?.variant?.product?.main_image?.image_path)"
                        :alt="item?.variant?.product?.name" class="tw-w-20 tw-h-20 tw-object-cover tw-rounded">
                    <div class="tw-flex-1">
                        <h6 class="tw-font-medium tw-mb-1">{{ item?.variant?.product?.name }}</h6>
                        <p class="tw-text-sm tw-text-gray-600 tw-mb-1">
                            <span v-if="item?.variant?.size">Size: {{ item.variant.size }}</span>
                            <span v-if="item?.variant?.color"> | Màu: {{ item.variant.color }}</span>
                        </p>
                        <p class="tw-text-sm tw-text-gray-500 tw-mb-1">
                            Còn lại: {{ item?.variant?.inventory?.quantity || 0 }} sản phẩm
                        </p>
                        <div class="tw-flex tw-justify-between tw-items-center">
                            <div class="tw-flex tw-items-center tw-gap-2">
                                <button class="tw-px-2 tw-py-1 tw-border tw-rounded hover:tw-bg-gray-100"
                                    @click="handleDecrease(item.id)" :disabled="item.quantity <= 1">-</button>
                                <span class="tw-text-sm">{{ item.quantity }}</span>
                                <button class="tw-px-2 tw-py-1 tw-border tw-rounded hover:tw-bg-gray-100"
                                    @click="handleIncrease(item.id)"
                                    :disabled="item.quantity >= item?.variant?.inventory?.quantity">+</button>
                            </div>
                            <span class="tw-font-medium">{{ formatPrice(item.price) }}</span>
                        </div>
                    </div>
                    <button class="tw-text-gray-400 hover:tw-text-red-500" @click="removeFromCart(item.id)"><i
                            class="bi bi-x-lg"></i></button>
                </div>
            </div>
            <!-- Cart Summary -->
            <div v-if="cartStore.cart?.length > 0" class="tw-mt-4 tw-pt-4 tw-border-t">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                    <span class="tw-font-medium">Tổng tiền:</span>
                    <span class="tw-font-bold tw-text-lg">{{ formatPrice(subtotal) }}</span>
                </div>
                <div class="tw-space-y-2">
                    <NuxtLink to="/gio-hang"
                        class="tw-block tw-w-full tw-border tw-border-[#81AACC] tw-text-[#81AACC] hover:tw-bg-[#81AACC] hover:tw-text-white tw-text-center tw-py-2 tw-rounded-md tw-transition-colors">
                        Xem chi tiết giỏ hàng
                    </NuxtLink>
                    <NuxtLink to="/thanh-toan"
                        class="tw-block tw-w-full tw-bg-[#81AACC] hover:tw-bg-[#6B8FA8] tw-text-white tw-text-center tw-py-2 tw-rounded-md tw-transition-colors">
                        Thanh toán
                    </NuxtLink>
                </div>
            </div>
        </div>
    </div>
    <div class="cart-overlay" :class="{ 'cart-overlay-open': isOpen }" @click="$emit('close')"></div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useNuxtApp } from '#app'
import { useCartStore } from '~/stores/useCartStore'

const { $config: runtimeConfig } = useNuxtApp()
const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    }
})

defineEmits(['close'])

const cartStore = useCartStore()

onMounted(() => {
    if (!cartStore.cart.length) {
        cartStore.fetchCart()
    }
})

// Watch isOpen prop
watch(() => props.isOpen, (newValue) => {
    if (newValue) {
        cartStore.fetchCart()
    }
})

const subtotal = computed(() => {
    return cartStore.cart?.reduce((total, item) => total + (item.price * item.quantity), 0) || 0
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const handleIncrease = async (cartId) => {
    try {
        const item = cartStore.cart.find(i => i.id === cartId)
        if (item) {
            await cartStore.updateCart(cartId, { quantity: item.quantity + 1 })
        }
    } catch (error) {
        console.log(error)
    }
}

const handleDecrease = async (cartId) => {
    try {
        const item = cartStore.cart.find(i => i.id === cartId)
        if (item && item.quantity > 1) {
            await cartStore.updateCart(cartId, { quantity: item.quantity - 1 })
        }
    } catch (error) {
        console.log(error)
    }
}

const removeFromCart = async (cartId) => {
    try {
        await cartStore.removeFromCart(cartId)
    } catch (error) {
        console.log(error)
    }
}

const getImageUrl = (path) => {
    if (!path) return '/default-image.jpg'
    if (path.startsWith('http://') || path.startsWith('https://')) return path
    if (path.startsWith('/storage/')) return runtimeConfig.public.apiBaseUrl.replace(/\/$/, '') + path
    if (path.startsWith('storage/')) return runtimeConfig.public.apiBaseUrl.replace(/\/$/, '') + '/' + path
    return runtimeConfig.public.apiBaseUrl.replace(/\/$/, '') + '/' + path
}
</script>

<style scoped>
.cart-panel {
    position: fixed;
    top: 0;
    right: -400px;
    width: 400px;
    height: 100vh;
    background-color: white;
    box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    transition: right 0.3s ease;
    visibility: visible;
    display: block;
}

.cart-panel-open {
    right: 0;
}

.cart-panel-content {
    height: 100%;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.cart-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.cart-overlay-open {
    opacity: 1;
    visibility: visible;
}
</style>
