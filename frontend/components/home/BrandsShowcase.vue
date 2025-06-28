<template>
    <div class="tw-mt-8">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <h2 class="tw-text-2xl tw-font-bold tw-text-gray-800">Thương hiệu nổi bật</h2>
            <NuxtLink to="/brands" class="tw-text-blue-600 tw-hover:text-blue-800 tw-font-medium tw-transition-colors">
                Xem tất cả →
            </NuxtLink>
        </div>

        <!-- Loading State -->
        <div v-if="loading"
            class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-3 md:tw-grid-cols-4 lg:tw-grid-cols-6 tw-gap-4">
            <div v-for="i in 12" :key="i" class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-4 tw-animate-pulse">
                <div class="tw-w-16 tw-h-16 tw-bg-gray-200 tw-rounded tw-mx-auto tw-mb-2"></div>
                <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-mb-1"></div>
                <div class="tw-h-3 tw-bg-gray-200 tw-rounded"></div>
            </div>
        </div>

        <!-- Brands Grid -->
        <div v-else class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-3 md:tw-grid-cols-4 lg:tw-grid-cols-6 tw-gap-4">
            <div v-for="brand in brands" :key="brand.id"
                class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-hover:shadow-md tw-transition-shadow tw-p-4 tw-flex tw-items-center tw-justify-center tw-cursor-pointer tw-group"
                @click="navigateToBrand(brand.slug || brand.id)">
                <div class="tw-text-center">
                    <div class="tw-w-16 tw-h-16 tw-mx-auto tw-mb-2 tw-flex tw-items-center tw-justify-center">
                        <img :src="getBrandLogo(brand)" :alt="brand.name"
                            class="tw-max-w-full tw-max-h-full tw-object-contain tw-group-hover:tw-scale-110 tw-transition-transform"
                            @error="handleImageError" />
                    </div>
                    <h3
                        class="tw-text-sm tw-font-medium tw-text-gray-700 tw-group-hover:tw-text-blue-600 tw-transition-colors">
                        {{ brand.name }}
                    </h3>
                    <p class="tw-text-xs tw-text-gray-500 tw-mt-1">
                        {{ brand.products_count || 0 }} sản phẩm
                    </p>
                </div>
            </div>
        </div>

        <!-- Featured Brand Banner -->
        <div v-if="featuredBrand"
            class="tw-mt-8 tw-bg-gradient-to-r tw-from-blue-600 tw-to-purple-600 tw-rounded-lg tw-p-6 tw-text-white">
            <div class="tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-justify-between">
                <div class="tw-flex-1 tw-mb-4 md:tw-mb-0">
                    <h3 class="tw-text-xl tw-font-bold tw-mb-2">{{ featuredBrand.name }} - Thương hiệu nổi bật</h3>
                    <p class="tw-text-blue-100 tw-mb-4">
                        Khám phá bộ sưu tập sản phẩm chất lượng cao từ {{ featuredBrand.name }}
                    </p>
                    <button @click="navigateToBrand(featuredBrand.slug || featuredBrand.id)"
                        class="tw-bg-white tw-text-blue-600 tw-px-6 tw-py-2 tw-rounded-lg tw-font-medium tw-hover:tw-bg-gray-100 tw-transition-colors">
                        Khám phá ngay
                    </button>
                </div>
                <div class="tw-flex-shrink-0">
                    <img :src="getBrandLogo(featuredBrand)" :alt="featuredBrand.name"
                        class="tw-w-24 tw-h-24 tw-object-contain" @error="handleImageError" />
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && brands.length === 0" class="tw-text-center tw-py-8">
            <p class="tw-text-gray-500">Chưa có thương hiệu nào</p>
        </div>
    </div>
</template>

<script setup>
import { useHome } from '../../composables/useHome'

const { getBrandsWithProductCount, logBrandStats } = useHome()

const brands = ref([])
const loading = ref(true)
const featuredBrand = ref(null)

const fetchBrands = async () => {
    try {
        loading.value = true
        const brandsData = await getBrandsWithProductCount()
        brands.value = brandsData

        if (brandsData.length > 0) {
            featuredBrand.value = brandsData.reduce((prev, current) =>
                (prev.products_count > current.products_count) ? prev : current
            )
        }

        await logBrandStats()
    } catch (error) {
        console.error('Error fetching brands:', error)
    } finally {
        loading.value = false
    }
}

const getBrandLogo = (brand) => {
    if (brand.logo) {
        return brand.logo.startsWith('http') ? brand.logo : `https://placehold.co/100x100?text=${brand.name.charAt(0)}`
    }
    return `https://placehold.co/100x100?text=${brand.name.charAt(0)}`
}

const handleImageError = (event) => {
    const brandName = event.target.alt || 'Brand'
    event.target.src = `https://placehold.co/100x100?text=${brandName.charAt(0)}`
}

const navigateToBrand = (brandId) => {
    navigateTo(`/brands/${brandId}`)
}

onMounted(() => {
    fetchBrands()
})
</script>