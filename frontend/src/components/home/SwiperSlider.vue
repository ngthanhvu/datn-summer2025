<!-- components/ImageSlider.vue -->
<template>
  <Swiper :modules="[Autoplay, Navigation, Pagination]" :slides-per-view="1" :loop="true" :autoplay="{ delay: 3000 }"
    navigation pagination class="w-full">
    <SwiperSlide v-for="(image, index) in images" :key="index">
      <img :src="image" class="w-full object-cover" />
    </SwiperSlide>
  </Swiper>
</template>

<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, Navigation, Pagination } from 'swiper/modules'

import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'

const images = [
  'https://theme.hstatic.net/200000696635/1001257291/14/slider_2.jpg?v=107',
  'https://theme.hstatic.net/200000696635/1001257291/14/slider_1.jpg?v=107',
]
</script>

<style scoped>
:deep(.swiper-button-next),
:deep(.swiper-button-prev) {
  width: 40px;
  height: 40px;
  background-color: rgba(255, 255, 255, 0.8);
  border-radius: 50%;
  color: #000;
}

:deep(.swiper-button-next:after),
:deep(.swiper-button-prev:after) {
  font-size: 20px;
}

:deep(.swiper-pagination-bullet) {
  width: 10px;
  height: 10px;
  background: #fff;
  opacity: 0.5;
}

:deep(.swiper-pagination-bullet-active) {
  opacity: 1;
  background: #fff;
}

:deep(.swiper-pagination-bullet) {
  width: 24px !important;
  height: 6px !important;
  border-radius: 3px !important;
  background: #d0d2d6 !important;
  transition: background 0.2s;
}

:deep(.swiper-pagination-bullet-active) {
  background: #81aacc !important;
}
</style>
