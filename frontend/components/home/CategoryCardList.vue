<template>
    <div>
        <div v-for="(products, category) in productsByCategory" :key="category" class="tw-mb-8">
            <h2 class="tw-text-xl tw-font-bold tw-mb-4">{{ category }}</h2>
            <swiper :modules="[SwiperPagination]" :slides-per-view="1" :space-between="0"
                :pagination="{ clickable: true }" :breakpoints="{
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
    </div>
</template>

<script setup>
import Card from './Card.vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Pagination as SwiperPagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import { ref, onMounted, computed } from 'vue'
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

// Giả sử mỗi product có product.category là tên danh mục
const productsByCategory = computed(() => {
    const grouped = {}
    for (const product of products.value) {
        const category = product.category || 'Khác'
        if (!grouped[category]) grouped[category] = []
        grouped[category].push(product)
    }
    return grouped
})
</script>

<style scoped>
:deep(.swiper-pagination-bullet-active) {
    background: #000;
}
</style>