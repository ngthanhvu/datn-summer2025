<template>
    <div class="mt-5 mb-5 bg-white p-8 rounded-[10px] border border-gray-200">
        <h2 class="text-2xl font-bold mb-6">Sản phẩm liên quan</h2>

        <!-- Mobile Slider -->
        <div class="lg:hidden">
            <swiper :slides-per-view="1.2" :space-between="16" :breakpoints="{
                '640': { slidesPerView: 2.2 },
                '768': { slidesPerView: 3.2 }
            }" class="w-full">
                <swiper-slide v-for="product in relatedProducts" :key="product?.id">
                    <div v-if="product?.slug" style="cursor:pointer">
                        <Card :product="product" />
                    </div>
                </swiper-slide>
            </swiper>
        </div>

        <!-- Desktop Grid -->
        <div class="hidden lg:grid lg:grid-cols-5 gap-6">
            <template v-if="relatedProducts.length">
                <template v-for="product in relatedProducts" :key="product.id">
                    <Card v-if="product?.slug" :product="product" @click="goToProduct(product.slug)" />
                </template>
            </template>
            <div v-else class="col-span-5 text-center">
                <p class="text-gray-500">Không có sản phẩm nào</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import Card from '../ui/Card.vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'

function goToProduct(slug) {
    window.location.href = `/san-pham/${slug}`
}

defineProps({
    relatedProducts: {
        type: Array,
        default: () => []
    }
})
</script>
