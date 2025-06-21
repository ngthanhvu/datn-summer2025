<template>
  <div class="tw-container tw-mx-auto tw-px-4 tw-py-4">
    <SwiperSlider />
    <ServiceFeatures />
    <CategoriesList />

    <NewProducts />

    <Banner />

    <CategoryProducts />

    <BrandsShowcase />

    <LatestReviews />

    <div class="tw-mt-3">
      <BlogsLatest />
    </div>
  </div>
</template>

<script setup>
import SwiperSlider from '~/components/home/SwiperSlider.vue';
import ServiceFeatures from '~/components/home/ServiceFeatures.vue';
import CategoriesList from '~/components/home/CategoriesList.vue';
import BlogsLatest from '~/components/home/BlogsLatest.vue';
import NewProducts from '~/components/home/NewProducts.vue';
import CategoryProducts from '~/components/home/CategoryProducts.vue';
import BrandsShowcase from '~/components/home/BrandsShowcase.vue';
import LatestReviews from '~/components/home/LatestReviews.vue';
import Banner from '@/components/home/Banner.vue';
useHead({
  title: 'Trang chủ - DEVGANG',
  meta: [
    { name: 'description', content: 'EGA MEN' },
  ],
});
</script>
