<template>
    <router-link :to="`/san-pham/${productProp.slug}`" class="block">
        <div class="w-full md:w-auto overflow-hidden group pb-2 sm:pb-3 relative rounded-[5px] bg-white">
            <!-- Image -->
            <div class="relative overflow-hidden mobile-image-container">
                <img :src="getMainImage" :alt="productProp.name"
                    class="w-full object-cover h-64 sm:h-96 transition-transform group-hover:scale-105 duration-300" />
                <div
                    class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex opacity-0 translate-y-4 
            group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">

                    <div class="relative group/cart">
                        <button @click.prevent="onQuickView"
                            class="bg-white text-black w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center 
                    transition duration-200 cursor-pointer group-hover/cart:bg-black group-hover/cart:text-white rounded-l">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.35 2.7a1 1 0 00.9 1.5H19M7 13L5.4 5M16 16a1 1 0 100 2 1 1 0 000-2zm-8 0a1 1 0 100 2 1 1 0 000-2z" />
                            </svg>
                        </button>
                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 text-xs rounded bg-black text-white opacity-0 
                    group-hover/cart:opacity-100 transition duration-200 whitespace-nowrap">
                            Thêm vào giỏ
                        </span>
                    </div>

                    <div class="relative group/view">
                        <button @click.prevent="onQuickView"
                            class="bg-white text-black w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center 
                    transition duration-200 cursor-pointer group-hover/view:bg-black group-hover/view:text-white rounded-r">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 text-xs rounded bg-black text-white opacity-0 
                    group-hover/view:opacity-100 transition duration-200 whitespace-nowrap">
                            Xem nhanh
                        </span>
                    </div>
                </div>
            </div>

            <div class="px-2 sm:px-3 mt-1 sm:mt-2">
                <p class="text-xs uppercase text-gray-400 truncate">Khác</p>
                <p class="text-sm sm:text-sm font-medium text-gray-900 line-clamp-2 leading-tight">
                    {{ productProp.name }}
                </p>

                <div class="flex items-center gap-1 sm:gap-2 mt-1 flex-wrap">
                    <p class="text-[#81AACC] font-semibold text-sm sm:text-base">
                        {{ formatPrice(productProp.discount_price || productProp.price) }}
                    </p>
                    <template v-if="productProp.discount_price && productProp.discount_price < productProp.price">
                        <p class="text-gray-400 line-through text-xs sm:text-sm">
                            {{ formatPrice(productProp.price) }}
                        </p>
                        <span class="bg-red-600 text-white text-xs rounded-full px-1 sm:px-2 py-[1px]">
                            -{{ calculateDiscount(productProp.price, productProp.discount_price) }}%
                        </span>
                    </template>
                </div>

                <div class="flex items-center justify-between mt-2 sm:mt-3">
                    <div class="flex items-center gap-1" v-if="productProp.variants && productProp.variants.length">
                        <div v-for="(variant, index) in displayedVariants" :key="index"
                            class="w-3 h-3 sm:w-4 sm:h-4 rounded-full border border-gray-300"
                            :style="{ backgroundColor: variant.color }" />
                        <span v-if="uniqueVariantCount > maxDisplayVariants" class="text-xs text-gray-500">
                            +{{ uniqueVariantCount - maxDisplayVariants }}
                        </span>
                    </div>
                    <FavoriteButton :product-slug="product.slug" @click.prevent />
                </div>
            </div>
        </div>
    </router-link>
</template>

<script setup>
import { computed } from 'vue'
import FavoriteButton from '../common/FavoriteButton.vue'
const props = defineProps({
    product: Object
})
const emit = defineEmits(['quick-view'])

const productProp = props.product
const maxDisplayVariants = 3

const getMainImage = computed(() => {
    if (!productProp.images || productProp.images.length === 0) {
        return 'https://via.placeholder.com/300x300?text=No+Image';
    }
    
    const main = productProp.images.find(img => img.is_main === 1);
    let imagePath = '';
    
    if (main && main.image_path) {
        imagePath = main.image_path;
    } else {
        const firstImage = productProp.images[0];
        if (firstImage && firstImage.image_path) {
            imagePath = firstImage.image_path;
        }
    }
    
    if (!imagePath) {
        return 'https://via.placeholder.com/300x300?text=No+Image';
    }
    
    // Nếu imagePath đã là URL đầy đủ, trả về luôn
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath;
    }
    
    // Nếu là đường dẫn tương đối, thêm base URL
    if (imagePath.startsWith('/storage/')) {
        return `${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'}${imagePath}`;
    }
    
    // Nếu không có /storage/, thêm /storage/ vào trước
    return `${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'}/storage/${imagePath}`;
})

const displayedVariants = computed(() => {
    const seen = new Set()
    const unique = []
    for (const variant of productProp.variants || []) {
        if (variant.color && !seen.has(variant.color)) {
            seen.add(variant.color)
            unique.push(variant)
        }
    }
    return unique.slice(0, maxDisplayVariants)
})

const uniqueVariantCount = computed(() => {
    return new Set((productProp.variants || []).map(v => v.color)).size
})

const formatPrice = price =>
    new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)

const calculateDiscount = (original, discount) =>
    Math.round(((original - discount) / original) * 100)

const addToCart = () => {
    console.log('Add to cart:', productProp)
}

const onQuickView = () => {
    emit('quick-view', productProp)
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .mobile-image-container {
        width: 236px;
        height: 320px;
        margin: 5px auto;
    }

    .mobile-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
}
</style>