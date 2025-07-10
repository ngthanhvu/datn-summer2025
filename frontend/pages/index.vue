<template>
  <div class="tw-container tw-mx-auto tw-px-4 tw-py-4">
    <SwiperSlider />
    <ServiceFeatures />
    <CategoriesList />

    <div class="tw-mt-3">
      <div class="tw-bg-white tw-p-8 tw-rounded-[5px]">
        <CouponList />
      </div>
    </div>

    <div class="tw-mt-3" v-if="shouldShowRecommend">
      <div class="tw-bg-white tw-p-8 tw-rounded-[5px]">
        <Trending />
      </div>
     </div>
     
    <div class="tw-mt-3">
      <div class="tw-bg-white tw-p-8 tw-rounded-[5px]">
        <FlashSale />
      </div>
    </div>

    <Suspense>
      <template #default>
        <NewProducts />
      </template>
      <template #fallback>
        <div class="tw-mt-3 tw-bg-white tw-p-8 tw-rounded-[5px] tw-animate-pulse">
          <div class="tw-h-8 tw-bg-gray-200 tw-rounded tw-mb-4"></div>
          <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 xl:tw-grid-cols-5 tw-gap-4">
            <div v-for="i in 5" :key="i" class="tw-bg-gray-200 tw-rounded-lg tw-h-80"></div>
          </div>
        </div>
      </template>
    </Suspense>

    <div class="tw-mt-3">
      <Banner />
    </div>

    <Suspense>
      <template #default>
        <CategoryProducts />
      </template>
      <template #fallback>
        <div class="tw-mt-3 tw-bg-white tw-p-8 tw-rounded-[5px] tw-animate-pulse">
          <div class="tw-h-8 tw-bg-gray-200 tw-rounded tw-mb-4"></div>
          <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 lg:tw-grid-cols-4 xl:tw-grid-cols-5 tw-gap-4">
            <div v-for="i in 5" :key="i" class="tw-bg-gray-200 tw-rounded-lg tw-h-80"></div>
          </div>
        </div>
      </template>
    </Suspense>

    <Suspense>
      <template #default>
        <BrandsShowcase />
      </template>
      <template #fallback>
        <div class="tw-mt-3 tw-bg-white tw-p-8 tw-rounded-[5px] tw-animate-pulse">
          <div class="tw-h-8 tw-bg-gray-200 tw-rounded tw-mb-4"></div>
          <div class="tw-grid tw-grid-cols-6 tw-gap-4">
            <div v-for="i in 6" :key="i" class="tw-bg-gray-200 tw-rounded-lg tw-h-20"></div>
          </div>
        </div>
      </template>
    </Suspense>

    <Suspense>
      <template #default>
        <LatestReviews />
      </template>
      <template #fallback>
        <div class="tw-mt-3 tw-bg-white tw-p-8 tw-rounded-[5px] tw-animate-pulse">
          <div class="tw-h-8 tw-bg-gray-200 tw-rounded tw-mb-4"></div>
          <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4">
            <div v-for="i in 3" :key="i" class="tw-bg-gray-200 tw-rounded-lg tw-h-40"></div>
          </div>
        </div>
      </template>
    </Suspense>

    <div class="tw-mt-3">
      <Suspense>
        <template #default>
          <BlogsLatest />
        </template>
        <template #fallback>
          <div class="tw-bg-white tw-p-8 tw-rounded-[5px] tw-animate-pulse">
            <div class="tw-h-8 tw-bg-gray-200 tw-rounded tw-mb-4"></div>
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-4">
              <div v-for="i in 3" :key="i" class="tw-bg-gray-200 tw-rounded-lg tw-h-60"></div>
            </div>
          </div>
        </template>
      </Suspense>
    </div>
  </div>
</template>

<script setup>
import { useAuth } from '~/composables/useAuth'
import { defineAsyncComponent, onMounted } from 'vue';
import { useHomeData } from '~/composables/useHomeData';

// Lazy load components để tăng tốc độ load trang chủ
const SwiperSlider = defineAsyncComponent(() => import('~/components/home/SwiperSlider.vue'))
const ServiceFeatures = defineAsyncComponent(() => import('~/components/home/ServiceFeatures.vue'))
const CategoriesList = defineAsyncComponent(() => import('~/components/home/CategoriesList.vue'))
const BlogsLatest = defineAsyncComponent(() => import('~/components/home/BlogsLatest.vue'))
const NewProducts = defineAsyncComponent(() => import('~/components/home/NewProducts.vue'))
const CategoryProducts = defineAsyncComponent(() => import('~/components/home/CategoryProducts.vue'))
const BrandsShowcase = defineAsyncComponent(() => import('~/components/home/BrandsShowcase.vue'))
const LatestReviews = defineAsyncComponent(() => import('~/components/home/LatestReviews.vue'))
const Banner = defineAsyncComponent(() => import('@/components/home/Banner.vue'))
const CouponList = defineAsyncComponent(() => import('~/components/home/CouponList.vue'))

const Trending = defineAsyncComponent(() => import('~/components/home/Trending.vue'))

const { user, isAuthenticated } = useAuth()
const shouldShowRecommend = computed(() => {
  if (!isAuthenticated.value || !user.value) return false
  return Boolean(user.value.username && user.value.gender && user.value.dateOfBirth)

const FlashSale = defineAsyncComponent(() => import('~/components/home/FlashSale.vue'))

// Initialize home data management
const { loadHomeData } = useHomeData()

// Load all home data when page mounts
onMounted(async () => {
  try {
    // Load all data in parallel for better performance
    await loadHomeData()
  } catch (error) {
    console.error('Error loading home data:', error)
  }
})

useHead({
  title: 'Trang chủ - DEVGANG',
  meta: [
    { name: 'description', content: 'EGA MEN' },
  ],
});
</script>
