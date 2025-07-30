<template>
    <div class="container mx-auto px-4 py-4">
        <SwiperSlider />
        <ServiceFeatures />
        <CategoriesList />

        <div class="mt-3">
            <div class="bg-white p-8 rounded-[5px]">
                <CouponList />
            </div>
        </div>
        <div class="mt-3">
            <NewProducts />
        </div>
        <div class="mt-3">
            <Banner />
        </div>

        <CategoriesProducts />
        <HotBrands />
        <ReviewLatest />
        <FeaturedBlogs />
    </div>

</template>

<script setup>
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
useHead({
    title: "Trang chủ | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Trang chủ"
        }
    ]
})
</script>

<style lang="scss" scoped></style>