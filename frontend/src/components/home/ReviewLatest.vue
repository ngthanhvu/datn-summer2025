<template>
    <div class="mt-3 bg-white p-8 rounded-[10px]">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Đánh giá gần nhất</h2>
            <router-link to="/reviews" class="text-blue-600 hover:text-blue-800 font-medium transition-colors">
                Xem tất cả →
            </router-link>
        </div>

        <div class="mb-10">
            <Swiper v-if="reviews.length > 3" class="custom-swiper-pagination" :modules="[Pagination]"
                :slides-per-view="1" :space-between="16" :breakpoints="{
                    640: { slidesPerView: 1.2 },
                    768: { slidesPerView: 2 },
                    1024: { slidesPerView: 3 }
                }" :pagination="{ clickable: true }">
                <SwiperSlide v-for="review in reviews" :key="review.id">
                    <div class="bg-white rounded-lg shadow-sm p-6 flex flex-col gap-2">
                        <ReviewCard :review="review" />
                    </div>
                </SwiperSlide>
            </Swiper>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="review in reviews" :key="review.id"
                    class="bg-white rounded-lg p-6 flex flex-col gap-2 border border-gray-100">
                    <ReviewCard :review="review" />
                </div>
            </div>
        </div>

        <div v-if="reviews.length === 0" class="text-center py-8">
            <p class="text-gray-500">Chưa có đánh giá nào</p>
        </div>
    </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import ReviewCard from '../ui/ReviewCard.vue'

import { useReviewStore } from '../../stores/review'

const reviewStore = useReviewStore()
const reviews = computed(() => reviewStore.reviews || [])

onMounted(() => {
    if (reviewStore.reviews.length === 0) {
        reviewStore.getLatestReview()
    }
})
</script>

<style scoped>
:deep(.custom-swiper-pagination .swiper-pagination) {
    position: static !important;
    display: flex;
    justify-content: center;
    margin-top: 1rem;
}

:deep(.custom-swiper-pagination .swiper-pagination-bullet) {
    width: 24px !important;
    height: 6px !important;
    border-radius: 3px !important;
    background: #d0d2d6 !important;
    transition: background 0.2s;
}

:deep(.custom-swiper-pagination .swiper-pagination-bullet-active) {
    background: #81aacc !important;
}
</style>
