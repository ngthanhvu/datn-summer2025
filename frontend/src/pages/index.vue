<template>
    <div class="container mx-auto px-4 py-4">
        <SwiperSlider />
        <ServiceFeatures />
        <CategoriesList />

        <LazyLoader @visible="loadCoupons">
            <div class="mt-3">
                <div class="bg-white p-5 rounded-[5px]">
                    <CouponList />
                </div>
            </div>
        </LazyLoader>
        <LazyLoader @visible="loadFlashSale">
            <div class="mt-3" v-if="showFlashSale">
                <FlashSale @has-flash-sale="handleFlashSaleStatus" />
            </div>
        </LazyLoader>

        <LazyLoader @visible="loadTrending">
            <div class="mt-3">
                <Trending />
            </div>
        </LazyLoader>

        <LazyLoader @visible="loadNewProducts">
            <div class="mt-3">
                <NewProducts />
            </div>
        </LazyLoader>

        <LazyLoader @visible="loadBestSellers">
            <BestSellers />
        </LazyLoader>

        <LazyLoader @visible="loadBanner">
            <div class="mt-3">
                <Banner />
            </div>
        </LazyLoader>

        <LazyLoader @visible="loadCategoriesProducts">
            <CategoriesProducts />
        </LazyLoader>

        <LazyLoader @visible="loadHotBrands">
            <HotBrands />
        </LazyLoader>

        <LazyLoader @visible="loadRecentlyViewed">
            <RecentlyViewed />
        </LazyLoader>

        <LazyLoader @visible="loadReviewLatest">
            <ReviewLatest />
        </LazyLoader>

        <LazyLoader @visible="loadFeaturedBlogs">
            <FeaturedBlogs />
        </LazyLoader>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useHead } from '@vueuse/head';
import Banner from '../components/home/Banner.vue';
import SwiperSlider from '../components/home/SwiperSlider.vue';
import ServiceFeatures from '../components/home/ServiceFeatures.vue';
import CategoriesList from '../components/home/CategoriesList.vue';
import CouponList from '../components/home/CouponList.vue';
import NewProducts from '../components/home/NewProducts.vue';
import CategoriesProducts from '../components/home/CategoriesProducts.vue';
import HotBrands from '../components/home/HotBrands.vue';
import ReviewLatest from '../components/home/ReviewLatest.vue';
import FeaturedBlogs from '../components/home/FeaturedBlogs.vue';
import FlashSale from '../components/home/FlashSale.vue';
import Trending from '../components/home/Trending.vue';
import LazyLoader from '../components/common/LazyLoader.vue';
import BestSellers from '../components/home/BestSellers.vue';
import RecentlyViewed from '../components/home/RecentlyViewed.vue';
import { useLazyLoading } from '../composable/useLazyLoading';

useHead({
    title: "Trang chủ | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Trang chủ"
        }
    ]
})

const showFlashSale = ref(true);
const handleFlashSaleStatus = (status) => {
    showFlashSale.value = status
}

const {
    loadingStates,
    loadedStates,
    data,
    loadComponentData,
    isComponentLoaded,
    isComponentLoading,
} = useLazyLoading()

const loadFlashSale = async () => {
    if (!isComponentLoaded('flashSale')) {
        await loadComponentData('flashSale', async () => {
        })
    }
}

const loadTrending = async () => {
    if (!isComponentLoaded('trending')) {
        await loadComponentData('trending', async () => {
        })
    }
}

const loadCoupons = async () => {
    if (!isComponentLoaded('coupons')) {
        await loadComponentData('coupons', async () => {
        })
    }
}

const loadNewProducts = async () => {
    if (!isComponentLoaded('newProducts')) {
        await loadComponentData('newProducts', async () => {
        })
    }
}
const loadBestSellers = async () => {
    if (!isComponentLoaded('bestSellers')) {
        await loadComponentData('bestSellers', async () => {
        })
    }
}

const loadBanner = async () => {
    if (!isComponentLoaded('banner')) {
        await loadComponentData('banner', async () => {
        })
    }
}

const loadCategoriesProducts = async () => {
    if (!isComponentLoaded('categoriesProducts')) {
        await loadComponentData('categoriesProducts', async () => {
        })
    }
}

const loadHotBrands = async () => {
    if (!isComponentLoaded('hotBrands')) {
        await loadComponentData('hotBrands', async () => {
        })
    }
}

const loadReviewLatest = async () => {
    if (!isComponentLoaded('reviewLatest')) {
        await loadComponentData('reviewLatest', async () => {
        })
    }
}

const loadFeaturedBlogs = async () => {
    if (!isComponentLoaded('featuredBlogs')) {
        await loadComponentData('featuredBlogs', async () => {
        })
    }
}

const loadRecentlyViewed = async () => {
    if (!isComponentLoaded('recentlyViewed')) {
        await loadComponentData('recentlyViewed', async () => {
        })
    }
}
</script>

<style lang="scss" scoped></style>