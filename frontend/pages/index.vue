<template>
  <div class="tw-container tw-mx-auto tw-px-4 tw-py-4">
    <SwiperSlider />
    <ServiceFeatures />
    <CategoriesList />
    <div class="tw-p-4">
      <CardList />
    </div>
    <div class="tw-mt-3 tw-p-4">
      <CategoryCardList />
    </div>
    <div class="tw-mt-3">
      <Banner />
    </div>
    <div class="tw-mt-3">
      <BlogsLatest />
    </div>
  </div>


</template>

<script setup>
import SwiperSlider from '~/components/home/SwiperSlider.vue';
import CardList from '~/components/home/CardList.vue';
import ServiceFeatures from '~/components/home/ServiceFeatures.vue';
import CategoriesList from '~/components/home/CategoriesList.vue';
import CategoryCardList from '~/components/home/CategoryCardList.vue';
import Banner from '~/components/home/Banner.vue';
import BlogsLatest from '~/components/home/BlogsLatest.vue';

useHead({
  title: 'Trang chủ - DEVGANG',
  meta: [
    { name: 'description', content: 'EGA MEN' },
  ],
});

</script>
