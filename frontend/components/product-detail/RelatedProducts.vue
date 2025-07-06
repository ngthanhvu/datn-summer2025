<template>
    <div class="tw-mt-5 tw-bg-white tw-p-8 tw-rounded-[10px] tw-border tw-border-bg-gray-200">
        <h2 class="tw-text-2xl tw-font-bold tw-mb-6">Sản phẩm liên quan</h2>
        <!-- Mobile Slider -->
        <div class="lg:tw-hidden">
            <swiper :slides-per-view="1.2" :space-between="16" :breakpoints="{
                '640': {
                    slidesPerView: 2.2,
                },
                '768': {
                    slidesPerView: 3.2,
                }
            }" class="tw-w-full">
                <swiper-slide v-for="product in relatedProducts" :key="product.id">
                    <Card :product="product" />
                </swiper-slide>
            </swiper>
        </div>
        <!-- Desktop Grid -->
        <div class="tw-hidden lg:tw-grid lg:tw-grid-cols-5 tw-gap-6">
            <Card v-for="product in relatedProducts" :key="product.id" :product="product" @quick-view="openQuickView" />
        </div>
        <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />
    </div>
</template>

<script setup>
import Card from '~/components/home/Card.vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import QuickView from '~/components/product-detail/Quick-view.vue'

// Quick View State
const showQuickView = ref(false)
const quickViewProduct = ref(null)

function openQuickView(product) {
    quickViewProduct.value = product
    showQuickView.value = true
}
function closeQuickView() {
    showQuickView.value = false
    quickViewProduct.value = null
}

defineProps({
    relatedProducts: {
        type: Array,
        default: () => []
    }
})
</script>