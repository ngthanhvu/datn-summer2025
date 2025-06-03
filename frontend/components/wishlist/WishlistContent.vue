<template>
    <div class="tw-container tw-mx-auto tw-py-8 tw-px-4">
        <!-- Header -->
        <div class="tw-mb-8">
            <h1 class="tw-text-2xl tw-font-semibold">Danh sách yêu thích</h1>
            <p class="tw-text-gray-600">Các sản phẩm bạn đã lưu để xem sau</p>
        </div>

        <!-- Grid of products -->
        <div
            class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-4 xl:tw-grid-cols-5 tw-gap-3 md:tw-gap-6 tw-max-w-[1440px] tw-mx-auto">
            <WishlistCard v-for="(item, index) in wishlistItems" :key="index" :product="item" @add-to-cart="addToCart"
                @quick-view="showQuickView" @remove="removeFromWishlist" />
        </div>

        <!-- Empty state -->
        <div v-if="wishlistItems.length === 0" class="tw-text-center tw-py-16">
            <i class="bi bi-heart tw-text-4xl tw-text-gray-400 tw-mb-4"></i>
            <h2 class="tw-text-xl tw-font-medium tw-mb-2">Danh sách yêu thích trống</h2>
            <p class="tw-text-gray-600 tw-mb-6">Bạn chưa có sản phẩm nào trong danh sách yêu thích</p>
            <NuxtLink to="/product"
                class="tw-bg-[#81AACC] tw-text-white tw-px-6 tw-py-3 tw-rounded-md hover:tw-bg-[#6B8FA8] tw-transition-colors">
                Khám phá sản phẩm
            </NuxtLink>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import WishlistCard from '~/components/wishlist/WishlistCard.vue'

// Sample data - replace with actual data from your store/API
const wishlistItems = ref([
    {
        image: 'https://product.hstatic.net/200000696635/product/frame_25_fb1b30c611ec4ebb88fc27d011201815_d572fde53b934b5ea502c2dd0a56a0d7_large.png',
        name: 'Áo Thun Nam Basic',
        category: 'Áo thun',
        price: 489000,
        originalPrice: 789000,
        colors: ['#000000', '#FFFFFF', '#FF0000']
    },
    {
        image: 'https://product.hstatic.net/200000696635/product/frame_25_fb1b30c611ec4ebb88fc27d011201815_d572fde53b934b5ea502c2dd0a56a0d7_large.png',
        name: 'Quần Jean Nam Slim Fit',
        category: 'Quần jean',
        price: 689000,
        originalPrice: 989000,
        colors: ['#000080', '#4169E1']
    },
    {
        image: 'https://product.hstatic.net/200000696635/product/frame_25_fb1b30c611ec4ebb88fc27d011201815_d572fde53b934b5ea502c2dd0a56a0d7_large.png',
        name: 'Áo Khoác Nam Bomber',
        category: 'Áo khoác',
        price: 989000,
        originalPrice: 1289000,
        colors: ['#000000', '#808080', '#A0522D']
    },
    {
        image: 'https://product.hstatic.net/200000696635/product/frame_25_fb1b30c611ec4ebb88fc27d011201815_d572fde53b934b5ea502c2dd0a56a0d7_large.png',
        name: 'Quần Short Nam Kaki',
        category: 'Quần short',
        price: 389000,
        originalPrice: 589000,
        colors: ['#F5F5DC', '#8B4513', '#000000']
    },
    {
        image: 'https://product.hstatic.net/200000696635/product/frame_25_fb1b30c611ec4ebb88fc27d011201815_d572fde53b934b5ea502c2dd0a56a0d7_large.png',
        name: 'Áo Sơ Mi Nam Dài Tay',
        category: 'Áo sơ mi',
        price: 589000,
        originalPrice: 789000,
        colors: ['#FFFFFF', '#87CEEB', '#FFB6C1', '#000000']
    }
])

const addToCart = (product) => {
    // Implement add to cart functionality
    console.log('Add to cart:', product)
}

const showQuickView = (product) => {
    // Implement quick view functionality
    console.log('Quick view:', product)
}

const removeFromWishlist = (product) => {
    // Implement remove from wishlist functionality
    const index = wishlistItems.value.findIndex(item => item === product)
    if (index !== -1) {
        wishlistItems.value.splice(index, 1)
    }
}
</script>

<style scoped>
:deep(.card-image) {
    height: 400px;
    /* Make product images taller */
    object-fit: cover;
}
</style>
