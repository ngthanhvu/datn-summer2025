<template>
    <section class="tw-bg-white tw-py-10">
        <div class="tw-container tw-mx-auto">
            <h2 class="tw-text-2xl tw-font-semibold tw-text-center tw-mb-10">Thời trang EGA</h2>

            <swiper :modules="[SwiperPagination]" :slides-per-view="5" :space-between="30"
                :pagination="{ clickable: true }" :breakpoints="{
                    '320': {
                        slidesPerView: 2,
                        spaceBetween: 20
                    },
                    '640': {
                        slidesPerView: 3,
                        spaceBetween: 20
                    },
                    '768': {
                        slidesPerView: 4,
                        spaceBetween: 30
                    },
                    '1024': {
                        slidesPerView: 5,
                        spaceBetween: 30
                    }
                }" class="categories-swiper">
                <swiper-slide v-for="(item, index) in categories" :key="index">
                    <NuxtLink :to="`/category/${item.label.toLowerCase().replace(/\s+/g, '-')}`"
                        class="tw-flex tw-flex-col tw-items-center tw-space-y-3 tw-transition-transform tw-duration-300 hover:tw-scale-110">
                        <!-- Image inside big circle -->
                        <div
                            class="tw-w-36 tw-h-36 tw-rounded-full tw-bg-gray-100 tw-flex tw-items-center tw-justify-center tw-overflow-hidden">
                            <img :src="item.image" :alt="item.label" class="tw-w-28 tw-h-28 tw-object-contain" />
                        </div>

                        <!-- Label -->
                        <p class="tw-text-base tw-font-medium">{{ item.label }}</p>
                        <p class="tw-text-sm tw-text-gray-500">{{ item.count }} sản phẩm</p>
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

const categories = [
    { label: 'Áo khoác', image: 'https://theme.hstatic.net/200000696635/1001257291/14/season_coll_2_img_large.png?v=107', count: 8 },
    { label: 'Áo sơ mi', image: 'https://theme.hstatic.net/200000696635/1001257291/14/season_coll_2_img_large.png?v=107', count: 6 },
    { label: 'Áo thun', image: 'https://theme.hstatic.net/200000696635/1001257291/14/season_coll_2_img_large.png?v=107', count: 7 },
    { label: 'Quần dài nam', image: 'https://theme.hstatic.net/200000696635/1001257291/14/season_coll_2_img_large.png?v=107', count: 5 },
    { label: 'Quần jeans', image: 'https://theme.hstatic.net/200000696635/1001257291/14/season_coll_2_img_large.png?v=107', count: 4 },
    { label: 'Quần short', image: 'https://theme.hstatic.net/200000696635/1001257291/14/season_coll_2_img_large.png?v=107', count: 5 }
]
</script>

<style scoped>
.categories-swiper {
    padding: 20px 0;
}

:deep(.swiper-pagination) {
    margin-bottom: -10px;
}

:deep(.swiper-pagination-bullet-active) {
    background: #000;
}
</style>