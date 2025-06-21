<template>
    <div class="tw-mt-8">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <h2 class="tw-text-2xl tw-font-bold tw-text-gray-800">Đánh giá gần nhất</h2>
            <NuxtLink to="/reviews" class="tw-text-blue-600 tw-hover:text-blue-800 tw-font-medium tw-transition-colors">
                Xem tất cả →
            </NuxtLink>
        </div>

        <div v-if="loading" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
            <div v-for="i in 3" :key="i" class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-animate-pulse">
                <div class="tw-flex tw-items-center tw-mb-4">
                    <div class="tw-w-12 tw-h-12 tw-bg-gray-200 tw-rounded-full tw-mr-3"></div>
                    <div class="tw-flex-1">
                        <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-mb-1"></div>
                        <div class="tw-h-3 tw-bg-gray-200 tw-rounded"></div>
                    </div>
                </div>
                <div class="tw-h-16 tw-bg-gray-200 tw-rounded tw-mb-4"></div>
                <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-mb-2"></div>
                <div class="tw-h-3 tw-bg-gray-200 tw-rounded"></div>
            </div>
        </div>

        <div v-else class="tw-mb-10">
            <Swiper v-if="latestReviews.length > 3" :modules="[Pagination]" :slides-per-view="1" :space-between="16"
                :breakpoints="{
                    640: { slidesPerView: 1.2 },
                    768: { slidesPerView: 2 },
                    1024: { slidesPerView: 3 }
                }" :pagination="{ clickable: true }">
                <SwiperSlide v-for="review in latestReviews" :key="review.id">
                    <div class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-flex tw-flex-col tw-gap-2">
                        <ReviewCard :review="review" />
                    </div>
                </SwiperSlide>
            </Swiper>

            <div v-else class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
                <div v-for="review in latestReviews" :key="review.id"
                    class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-flex tw-flex-col tw-gap-2">
                    <ReviewCard :review="review" />
                </div>
            </div>
        </div>

        <div v-if="!loading && latestReviews.length === 0" class="tw-text-center tw-py-8">
            <p class="tw-text-gray-500">Chưa có đánh giá nào</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useHome } from '../../composables/useHome'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import 'swiper/css/pagination'
import { Pagination } from 'swiper/modules'
import ReviewCard from './ReviewCard.vue'

const { getLatestReviews, getReviewStats } = useHome()
const latestReviews = ref([])
const reviewStats = ref(null)
const loading = ref(true)

// Fetch latest reviews
const fetchLatestReviews = async () => {
    try {
        loading.value = true
        const reviews = await getLatestReviews(6)
        latestReviews.value = reviews
    } catch (error) {
        console.error('Error fetching latest reviews:', error)
    } finally {
        loading.value = false
    }
}

// Fetch stats (if used)
const fetchReviewStats = async () => {
    try {
        const stats = await getReviewStats()
        reviewStats.value = stats
    } catch (error) {
        console.error('Error fetching review stats:', error)
    }
}

onMounted(async () => {
    await Promise.all([
        fetchLatestReviews(),
        fetchReviewStats()
    ])
})
</script>