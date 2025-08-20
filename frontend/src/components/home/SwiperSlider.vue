<!-- components/ImageSlider.vue -->
<template>
  <Swiper :modules="[Autoplay, Navigation, Pagination]" :slides-per-view="1" :loop="true" :autoplay="{ delay: 3000 }"
    navigation pagination class="w-full">
    <!-- <SwiperSlide v-for="(image, index) in images" :key="index">
      <img :src="image" class="w-full object-cover" /> -->
    <SwiperSlide v-for="(image, index) in bannerImages" :key="index">
      <img @click="redirectToProductPage" :src="image" :alt="`Banner ${index + 1}`"
        class="w-full object-cover cursor-pointer" @error="handleImageError(index)" />
    </SwiperSlide>
  </Swiper>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { useRouter } from 'vue-router'
import { Autoplay, Navigation, Pagination } from 'swiper/modules'
import { useSetting } from '../../composable/useSettingsApi'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'

const { settings, fetchSettings } = useSetting()

const router = useRouter()

const defaultImages = [
  'https://i.imgur.com/sz2mSUp.png',
  'https://i.imgur.com/QQsOZz8.png',
]

onMounted(async () => {
  await fetchSettings()
})

const bannerImages = computed(() => {
  const bannersData = settings.value.banners

  if (!bannersData) return defaultImages

  let parsed = []

  if (typeof bannersData === 'string') {
    if (bannersData.startsWith('[') && bannersData.endsWith(']')) {
      try {
        parsed = JSON.parse(bannersData)
      } catch {
        parsed = bannersData.split(',')
      }
    } else {
      parsed = bannersData.split(',')
    }
  } else if (Array.isArray(bannersData)) {
    parsed = bannersData
  }

  const clean = parsed.filter(img => img && img.trim())

  return clean.length > 0 ? clean : defaultImages
})

const handleImageError = (index) => {
  console.error(`Không thể load banner ${index + 1}`)
}

const redirectToProductPage = () => {
  router.push('/san-pham')
}
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
