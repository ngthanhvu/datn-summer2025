<template>
    <div class="mt-3 bg-white p-4 md:p-8 rounded-[10px]">
        <div class="flex justify-between items-center mb-4 md:mb-6">
            <h2 class="text-lg md:text-2xl font-semibold text-gray-800">Thương hiệu nổi bật</h2>
            <router-link to="/brands"
                class="text-blue-600 hover:text-blue-800 font-medium transition-colors text-sm md:text-base">
                Xem tất cả →
            </router-link>
        </div>

        <!-- Brands Swiper -->
        <div class="relative">
            <swiper :modules="[SwiperNavigation, SwiperPagination]" :slides-per-view="2" :space-between="16"
                :navigation="true" :pagination="{ clickable: true }" :breakpoints="{
                    640: {
                        slidesPerView: 3,
                        spaceBetween: 20
                    },
                    768: {
                        slidesPerView: 4,
                        spaceBetween: 24
                    },
                    1024: {
                        slidesPerView: 5,
                        spaceBetween: 24
                    },
                    1280: {
                        slidesPerView: 6,
                        spaceBetween: 24
                    }
                }" class="brands-swiper">
                <swiper-slide v-for="brand in brands" :key="brand.id">
                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-3 md:p-4 flex items-center justify-center cursor-pointer group border border-gray-100 h-full"
                        @click="navigateToBrand(brand.slug || brand.id)">
                        <div class="text-center w-full">
                            <div
                                class="w-10 h-10 md:w-16 md:h-16 mx-auto mb-1 md:mb-2 flex items-center justify-center">
                                <img :src="getBrandLogo(brand)" :alt="brand.name"
                                    class="max-w-full max-h-full object-contain group-hover:scale-110 transition-transform"
                                    @error="handleImageError" />
                            </div>
                            <h3
                                class="text-xs md:text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors line-clamp-2">
                                {{ brand.name }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">{{ brand.products_count || 0 }} sản phẩm</p>
                        </div>
                    </div>
                </swiper-slide>
            </swiper>
        </div>

        <!-- Featured Brand Banner -->
        <div v-if="featuredBrand"
            class="mt-6 md:mt-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-4 md:p-6 text-white">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="flex-1 mb-3 md:mb-0 text-center md:text-left">
                    <h3 class="text-base md:text-xl font-bold mb-2">{{ featuredBrand.name }} - Thương hiệu nổi bật</h3>
                    <p class="text-blue-100 mb-3 md:mb-4 text-xs md:text-base leading-relaxed">
                        Khám phá bộ sưu tập sản phẩm chất lượng cao từ {{ featuredBrand.name }}
                    </p>
                    <button @click="navigateToBrand(featuredBrand.slug || featuredBrand.id)"
                        class="bg-white text-blue-600 px-4 md:px-6 py-2 rounded-lg font-medium hover:bg-gray-100 transition-colors text-sm md:text-base">
                        Khám phá ngay
                    </button>
                </div>
                <div class="flex-shrink-0 mt-3 md:mt-0">
                    <img :src="getBrandLogo(featuredBrand)" :alt="featuredBrand.name"
                        class="w-16 h-16 md:w-24 md:h-24 object-contain" @error="handleImageError" />
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="brands.length === 0" class="text-center py-8">
            <p class="text-gray-500">Chưa có thương hiệu nào</p>
        </div>
    </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useBrandStore } from '../../stores/brands'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Navigation as SwiperNavigation, Pagination as SwiperPagination } from 'swiper/modules'

// Import Swiper styles
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'

const brandStore = useBrandStore()
const brands = computed(() => brandStore.brands)

onMounted(() => {
    if (brandStore.brands.length === 0) {
        brandStore.fetchBrands()
    }
})

const featuredBrand = computed(() => {
    return brands.value.reduce((prev, curr) =>
        prev.products_count > curr.products_count ? prev : curr,
        brands.value[0]
    )
})
const getBrandLogo = (brand) => {
    if (brand.image) return brand.image
    return `https://placehold.co/100x100?text=${brand.name.charAt(0)}`
}

const handleImageError = (event) => {
    const name = event.target.alt || 'B'
    event.target.src = `https://placehold.co/100x100?text=${name.charAt(0)}`
}

const navigateToBrand = (brandId) => {
    // Nếu bạn đang dùng vue-router trong setup
    window.location.href = `/brands/${brandId}`
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

/* Custom Swiper Styles */
.brands-swiper {
    padding-bottom: 40px;
}

.brands-swiper :deep(.swiper-button-next),
.brands-swiper :deep(.swiper-button-prev) {
    color: #3b82f6;
    background: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.brands-swiper :deep(.swiper-button-next:after),
.brands-swiper :deep(.swiper-button-prev:after) {
    font-size: 14px;
    font-weight: bold;
}

.brands-swiper :deep(.swiper-pagination-bullet) {
    background: #3b82f6;
    opacity: 0.3;
}

.brands-swiper :deep(.swiper-pagination-bullet-active) {
    opacity: 1;
}

.brands-swiper :deep(.swiper-slide) {
    height: auto;
}
</style>
