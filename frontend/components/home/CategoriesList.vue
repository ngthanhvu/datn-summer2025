<template>
    <section class="tw-bg-white tw-py-10 tw-rounded-[5px]">
        <div class="tw-container tw-mx-auto">
            <h2 class="tw-text-2xl tw-font-semibold tw-text-center tw-mb-10">Thời trang DevGang</h2>

            <!-- Loading State -->
            <div v-if="loading" class="tw-flex tw-gap-6 tw-justify-center tw-mb-6">
                <div v-for="i in 8" :key="i" class="tw-flex tw-flex-col tw-items-center">
                    <div class="tw-w-36 tw-h-36 tw-rounded-full tw-bg-gray-200 tw-animate-pulse"></div>
                    <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-mt-4 tw-mb-2 tw-w-24"></div>
                    <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-w-16"></div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!categories.length" class="tw-text-center tw-text-gray-500 tw-my-6">
                Không có dữ liệu
            </div>

            <!-- Swiper Content -->
            <swiper v-else :modules="[SwiperPagination]" :slides-per-view="5" :space-between="8"
                :pagination="{ clickable: true }" :breakpoints="{
                    '320': {
                        slidesPerView: 2,
                        spaceBetween: 8
                    },
                    '640': {
                        slidesPerView: 3,
                        spaceBetween: 8
                    },
                    '768': {
                        slidesPerView: 4,
                        spaceBetween: 8
                    },
                    '1024': {
                        slidesPerView: 6,
                        spaceBetween: 8
                    }
                }" class="categories-swiper">
                <swiper-slide v-for="category in categories" :key="category.id">
                    <NuxtLink :to="`/category/${category.slug}`"
                        class="tw-flex tw-flex-col tw-items-center tw-space-y-3 tw-transition-transform tw-duration-300 hover:tw-scale-90 tw-pb-3">
                        <!-- Image inside big circle -->
                        <div
                            class="tw-w-36 tw-h-36 tw-rounded-full tw-border tw-border-gray-200 tw-flex tw-items-center tw-justify-center tw-overflow-hidden">
                            <img :src="category.image" :alt="category.name" class="tw-w-28 tw-h-28 tw-object-contain" />
                        </div>

                        <!-- Label -->
                        <p class="tw-text-base tw-font-medium">{{ category.name }}</p>
                        <p class="tw-text-sm tw-text-gray-500">{{ category.products_count || 0 }} sản phẩm</p>
                    </NuxtLink>
                </swiper-slide>
            </swiper>
        </div>
    </section>
</template>

<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Pagination as SwiperPagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import { ref, onMounted } from 'vue'

const { getCategories, logCategoryStats } = useCategory()
const categories = ref([])
const loading = ref(true)

onMounted(async () => {
    try {
        loading.value = true
        categories.value = await getCategories()
        await logCategoryStats()
    } catch (error) {
        console.error('Error fetching categories:', error)
    } finally {
        loading.value = false
    }
})
</script>

<style scoped>
:deep(.swiper-pagination) {
    margin-bottom: -10px;
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
