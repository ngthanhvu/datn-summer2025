<template>
    <div class="tw-container tw-mx-auto tw-px-0 md:tw-px-4">
        <swiper :modules="[SwiperPagination]" :slides-per-view="1" :space-between="0" :pagination="{ clickable: true }"
            :breakpoints="{
                '640': { slidesPerView: 2, spaceBetween: 16 },
                '768': { slidesPerView: 3, spaceBetween: 20 },
                '1024': { slidesPerView: 4, spaceBetween: 24 },
                '1280': { slidesPerView: 5, spaceBetween: 24 }
            }" class="tw-w-full">
            <swiper-slide v-for="product in products" :key="product.id">
                <Card :product="product" />
            </swiper-slide>
        </swiper>
    </div>
</template>

<script setup>
import Card from './Card.vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Pagination as SwiperPagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import { ref, onMounted } from 'vue'
import { useProducts } from '../../composables/useProducts'

const { getProducts } = useProducts()
const products = ref([])

onMounted(async () => {
    try {
        products.value = await getProducts()
    } catch (error) {
        console.error('Error fetching products:', error)
    }
})
</script>

<style scoped>
:deep(.swiper-pagination-bullet-active) {
    background: #000;
}
</style>