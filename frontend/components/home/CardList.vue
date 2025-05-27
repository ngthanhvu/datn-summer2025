<template>
    <div class="tw-container tw-mx-auto tw-px-0 md:tw-px-4">
        <swiper :modules="[SwiperPagination]" :slides-per-view="1" :space-between="0" :pagination="{ clickable: true }"
            :breakpoints="{
                '640': { slidesPerView: 2, spaceBetween: 16 },
                '768': { slidesPerView: 3, spaceBetween: 20 },
                '1024': { slidesPerView: 4, spaceBetween: 24 },
                '1280': { slidesPerView: 5, spaceBetween: 24 }
            }" class="tw-w-full">
            <swiper-slide v-for="(item, index) in items" :key="index">
                <Card />
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

// Sample data - you can replace this with your actual data
const items = Array(5).fill(null)
</script>

<style scoped>
:deep(.swiper-pagination-bullet-active) {
    background: #000;
}
</style>