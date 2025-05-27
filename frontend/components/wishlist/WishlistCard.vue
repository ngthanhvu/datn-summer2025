<template>
    <div class="tw-w-full tw-overflow-hidden tw-group tw-pb-4 tw-relative">
        <!-- Image wrapper -->
        <div class="tw-relative tw-overflow-hidden">
            <img :src="product.image || 'https://product.hstatic.net/200000696635/product/frame_25_fb1b30c611ec4ebb88fc27d011201815_d572fde53b934b5ea502c2dd0a56a0d7_large.png'"
                :alt="product.name || 'Product Image'"
                class="tw-w-full tw-object-cover tw-h-[400px] tw-transition-transform group-hover:tw-scale-105 tw-duration-300" />

            <!-- Hover overlay -->
            <div
                class="tw-absolute tw-inset-0 tw-bg-black/10 tw-opacity-0 group-hover:tw-opacity-100 tw-transition-opacity tw-duration-300">
            </div>

            <!-- Action buttons -->
            <div
                class="tw-absolute tw-bottom-2 tw-left-1/2 -tw-translate-x-1/2 tw-flex tw-gap-2 tw-opacity-0 tw-translate-y-4 group-hover:tw-opacity-100 group-hover:tw-translate-y-0 tw-transition-all tw-duration-300">
                <!-- Add to cart -->
                <button @click="addToCart"
                    class="tw-bg-white tw-rounded tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-shadow hover:tw-bg-gray-100 tw-transition tw-duration-200"
                    title="Thêm vào giỏ">
                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.35 2.7a1 1 0 00.9 1.5H19M7 13L5.4 5M16 16a1 1 0 100 2 1 1 0 000-2zm-8 0a1 1 0 100 2 1 1 0 000-2z" />
                    </svg>
                </button>

                <!-- Quick view -->
                <button @click="quickView"
                    class="tw-bg-white tw-rounded tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-shadow hover:tw-bg-gray-100 tw-transition tw-duration-200"
                    title="Xem nhanh">
                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>

                <!-- Remove from wishlist -->
                <button @click="removeFromWishlist"
                    class="tw-bg-white tw-rounded tw-w-10 tw-h-10 tw-flex tw-items-center tw-justify-center tw-shadow hover:tw-bg-red-100 tw-transition tw-duration-200"
                    title="Xóa khỏi yêu thích">
                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-5 tw-w-5 tw-text-red-500" fill="currentColor"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 21.682l-7.682-7.682a4.5 4.5 0 010-6.364z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Product info -->
        <div class="tw-px-3 tw-mt-4">
            <p class="tw-text-xs tw-uppercase tw-text-gray-400">{{ product.category || 'Khác' }}</p>
            <p class="tw-text-sm tw-font-medium tw-text-gray-900 tw-mt-1">{{ product.name || 'Tên sản phẩm' }}</p>

            <!-- Price -->
            <div class="tw-flex tw-items-center tw-gap-2 tw-mt-2">
                <p class="tw-text-blue-600 tw-font-semibold tw-text-base">{{ formatPrice(product.price) }}</p>
                <template v-if="product.originalPrice">
                    <p class="tw-text-gray-400 tw-line-through tw-text-sm">{{ formatPrice(product.originalPrice) }}</p>
                    <span class="tw-bg-red-600 tw-text-white tw-text-xs tw-rounded-full tw-px-2 tw-py-[1px]">
                        {{ calculateDiscount(product.price, product.originalPrice) }}%
                    </span>
                </template>
            </div>

            <!-- Color variants -->
            <div class="tw-flex tw-items-center tw-gap-1 tw-mt-3">
                <div v-for="(color, index) in product.colors" :key="index"
                    class="tw-w-4 tw-h-4 tw-rounded-full tw-border tw-border-gray-300"
                    :style="{ backgroundColor: color }">
                </div>
                <span v-if="product.colors?.length > 3" class="tw-text-xs tw-text-gray-500">
                    +{{ product.colors.length - 3 }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    product: {
        type: Object,
        default: () => ({
            image: '',
            name: '',
            category: '',
            price: 0,
            originalPrice: 0,
            colors: []
        })
    }
})

const emit = defineEmits(['add-to-cart', 'quick-view', 'remove'])

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const calculateDiscount = (price, originalPrice) => {
    return Math.round(((originalPrice - price) / originalPrice) * 100)
}

const addToCart = () => {
    emit('add-to-cart', props.product)
}

const quickView = () => {
    emit('quick-view', props.product)
}

const removeFromWishlist = () => {
    emit('remove', props.product)
}
</script>

<style scoped>
/* Add any additional styling here */
</style>