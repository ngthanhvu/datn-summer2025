<template>
    <div class="mt-3 bg-white p-4 md:px-8 rounded-[10px]">
        <div class="flex justify-between items-center mb-4 md:mb-6">
            <h2 class="text-lg md:text-2xl font-semibold text-gray-800">Đánh giá gần nhất</h2>
        </div>

        <div class="mb-6 md:mb-4">
            <!-- Mobile: Horizontal scroll -->
            <div class="flex gap-4 overflow-x-auto scroll-smooth md:hidden">
                <div v-for="review in filteredReviews" :key="review.id" class="flex-shrink-0 w-80">
                    <div class="bg-white rounded-lg shadow-sm p-4 md:p-6 flex flex-col gap-2">
                        <ReviewCard :review="review" />
                    </div>
                </div>
            </div>

            <!-- Desktop: Swiper for more than 3 reviews -->
            <Swiper v-if="filteredReviews.length > 3" class="custom-swiper-pagination hidden md:block"
                :modules="[Pagination]" :slides-per-view="1" :space-between="16" :breakpoints="{
                    640: { slidesPerView: 1.2 },
                    768: { slidesPerView: 2 },
                    1024: { slidesPerView: 3 }
                }" :pagination="{ clickable: true }">
                <SwiperSlide v-for="review in filteredReviews" :key="review.id">
                    <div class="bg-white rounded-lg shadow-sm p-6 flex flex-col gap-2">
                        <ReviewCard :review="review" />
                    </div>
                </SwiperSlide>
            </Swiper>

            <!-- Desktop: Grid for 3 or fewer reviews -->
            <div v-else-if="filteredReviews.length <= 3"
                class="hidden md:grid md:grid-cols-1 md:md:grid-cols-2 md:lg:grid-cols-3 md:gap-6">
                <div v-for="review in filteredReviews" :key="review.id"
                    class="bg-white rounded-lg p-6 flex flex-col gap-2 border border-gray-100">
                    <ReviewCard :review="review" />
                </div>
            </div>
        </div>

        <div v-if="filteredReviews.length === 0" class="text-center py-8">
            <p class="text-gray-500">Chưa có đánh giá 4-5 sao nào</p>
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

// Lọc chỉ những đánh giá có rating 4 và 5 sao
const filteredReviews = computed(() => {
    return reviews.value.filter(review => review.rating >= 4)
})

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
