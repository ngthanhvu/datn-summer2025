<template>
    <section class="bg-white py-10 rounded-[5px]">
        <div class="container mx-auto">
            <h2 class="text-2xl font-semibold text-center mb-10">Thời trang DevGang</h2>

            <!-- Loading State -->
            <div v-if="categoryStore.loading" class="flex gap-6 justify-center mb-6">
                <div v-for="i in 6" :key="i" class="flex flex-col items-center">
                    <div class="w-36 h-36 rounded-full bg-gray-200 animate-pulse"></div>
                    <div class="h-4 bg-gray-200 rounded mt-4 mb-2 w-24"></div>
                    <div class="h-4 bg-gray-200 rounded w-16"></div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!categoryStore.categories.length" class="text-center text-gray-500 my-6">
                Không có dữ liệu
            </div>

            <!-- Swiper Content -->
            <swiper v-else :modules="[SwiperPagination]" :slides-per-view="5" :space-between="8"
                :pagination="{ clickable: true }" :breakpoints="{
                    320: { slidesPerView: 2, spaceBetween: 8 },
                    640: { slidesPerView: 3, spaceBetween: 8 },
                    768: { slidesPerView: 4, spaceBetween: 8 },
                    1024: { slidesPerView: 6, spaceBetween: 8 }
                }" class="categories-swiper">
                <swiper-slide v-for="category in categoryStore.categories" :key="category.id">
                    <div
                        class="flex flex-col items-center space-y-3 transition-transform duration-300 hover:scale-90 pb-3 cursor-pointer"
                        @click="goToCategory(category)">
                        <div
                            class="w-36 h-36 rounded-full border border-gray-200 flex items-center justify-center overflow-hidden">
                            <img :src="category.image" :alt="category.name" class="w-28 h-28 object-contain" />
                        </div>
                        <p class="text-base font-medium">{{ category.name }}</p>
                        <p class="text-sm text-gray-500">{{ productCounts[category.id] ?? category.products_count ?? 0 }} sản phẩm</p>
                    </div>
                </swiper-slide>
            </swiper>
        </div>
    </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Pagination as SwiperPagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import { useCategoryStore } from '../../stores/categories'
import { useProducts } from '../../composable/useProducts'

const categoryStore = useCategoryStore()
const router = useRouter()
const { getProducts } = useProducts()

const productCounts = ref({})

onMounted(async () => {
    await categoryStore.fetchCategories()
    try {
        const cats = categoryStore.categories || []
        await Promise.all(
            cats.map(async (c) => {
                try {
                    const res = await getProducts({ category: c.id })
                    productCounts.value[c.id] = res?.pagination?.total ?? (res?.products?.length ?? 0)
                } catch (e) {
                    productCounts.value[c.id] = c.products_count ?? 0
                }
            })
        )
    } catch {}
})

const goToCategory = (category) => {
    if (!category) return
    router.push({ path: `/danh-muc/${category.slug || category.id}` })
}
</script>

<style scoped>
::deep(.swiper-pagination) {
    margin-bottom: -10px;
}

::deep(.swiper-pagination-bullet) {
    width: 24px !important;
    height: 6px !important;
    border-radius: 3px !important;
    background: #d0d2d6 !important;
    transition: background 0.2s;
}

::deep(.swiper-pagination-bullet-active) {
    background: #81aacc !important;
}
</style>
