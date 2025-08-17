<template>
    <div class="mt-3 bg-white p-4 md:px-8 rounded-[10px]">
        <div class="flex justify-between items-center mb-4 md:mb-6">
            <h2 class="text-lg md:text-2xl font-semibold text-gray-800">Thương hiệu nổi bật</h2>
        </div>

        <!-- Brands Swiper -->
        <div class="relative">
            <swiper :modules="[SwiperNavigation]" :slides-per-view="2" :space-between="16" :navigation="true"
                :breakpoints="{
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
                    <div class="p-3 md:p-4 flex items-center justify-center h-full">
                        <div class="text-center w-full">
                            <div
                                class="w-10 h-10 md:w-16 md:h-16 mx-auto mb-1 md:mb-2 flex items-center justify-center">
                                <img :src="getBrandLogo(brand)" :alt="brand.name"
                                    class="max-w-full max-h-full object-contain" @error="handleImageError" />
                            </div>
                            <h3 class="text-xs md:text-sm font-medium text-gray-700 line-clamp-2">
                                {{ brand.name }}
                            </h3>
                        </div>
                    </div>
                </swiper-slide>
            </swiper>
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
import { Navigation as SwiperNavigation } from 'swiper/modules'

// Import Swiper styles
import 'swiper/css'
import 'swiper/css/navigation'

const brandStore = useBrandStore()
const brands = computed(() => brandStore.brands)

onMounted(() => {
    if (brandStore.brands.length === 0) {
        brandStore.fetchBrands()
    }
})

const getBrandLogo = (brand) => {
    if (brand.image) return brand.image
    return `https://placehold.co/100x100?text=${brand.name.charAt(0)}`
}

const handleImageError = (event) => {
    const name = event.target.alt || 'B'
    event.target.src = `https://placehold.co/100x100?text=${name.charAt(0)}`
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

.brands-swiper :deep(.swiper-slide) {
    height: auto;
}
</style>
