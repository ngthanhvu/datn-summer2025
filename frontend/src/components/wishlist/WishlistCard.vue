<template>
    <div class="w-full overflow-hidden group pb-4 relative">
        <!-- Image wrapper -->
        <div class="relative overflow-hidden">
            <img :src="product.image" :alt="product.name"
                class="w-full object-cover h-[400px] transition-transform group-hover:scale-105 duration-300" />

            <!-- Hover overlay -->
            <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            </div>

            <!-- Action buttons -->
            <div
                class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-2 opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
                <!-- Add to cart -->
                <button @click="addToCart"
                    class="bg-white rounded w-10 h-10 flex items-center justify-center shadow hover:bg-gray-100 transition duration-200"
                    title="Thêm vào giỏ">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.35 2.7a1 1 0 00.9 1.5H19M7 13L5.4 5M16 16a1 1 0 100 2 1 1 0 000-2zm-8 0a1 1 0 100 2 1 1 0 000-2z" />
                    </svg>
                </button>

                <!-- Quick view -->
                <button @click="quickView"
                    class="bg-white rounded w-10 h-10 flex items-center justify-center shadow hover:bg-gray-100 transition duration-200 relative group/quickview"
                    title="Xem chi tiết">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span
                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover/quickview:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                        Xem chi tiết
                    </span>
                </button>

                <!-- Remove from wishlist -->
                <button @click="removeFromWishlist"
                    class="bg-white rounded w-10 h-10 flex items-center justify-center shadow hover:bg-red-100 transition duration-200"
                    title="Xóa khỏi yêu thích">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="currentColor"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 21.682l-7.682-7.682a4.5 4.5 0 010-6.364z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Product info -->
        <div class="px-3 mt-4">
            <p class="text-xs uppercase text-gray-400">{{ product.category || 'Khác' }}</p>
            <p class="text-sm font-medium text-gray-900 mt-1">{{ product.name || 'Tên sản phẩm' }}</p>

            <!-- Price -->
            <div class="flex items-center gap-2 mt-2">
                <p class="text-blue-600 font-semibold text-base">{{ formatPrice(product.price) }}</p>
                <template v-if="product.originalPrice">
                    <p class="text-gray-400 line-through text-sm">{{ formatPrice(product.originalPrice) }}</p>
                    <span class="bg-red-600 text-white text-xs rounded-full px-2 py-[1px]">
                        {{ calculateDiscount(product.price, product.originalPrice) }}%
                    </span>
                </template>
            </div>

            <!-- Color variants -->
            <div class="flex items-center gap-1 mt-3">
                <div v-for="(color, index) in product.colors" :key="index"
                    class="w-4 h-4 rounded-full border border-gray-300" :style="{ backgroundColor: color }">
                </div>
                <span v-if="product.colors?.length > 3" class="text-xs text-gray-500">
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