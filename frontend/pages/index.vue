<template>
  <div class="tw-container tw-mx-auto tw-px-4 tw-py-4">
    <SwiperSlider />
    <ServiceFeatures />
    <CategoriesList />
    <div class="tw-p-4">
      <CardList />
    </div>
  </div>


</template>

<script setup>
import SwiperSlider from '~/components/home/SwiperSlider.vue';
import CardList from '~/components/home/CardList.vue';
import ServiceFeatures from '~/components/home/ServiceFeatures.vue';
import CategoriesList from '~/components/home/CategoriesList.vue';
useHead({
  title: 'Trang chủ - DEVGANG',
  meta: [
    { name: 'description', content: 'EGA MEN' },
  ],
});

</script>
