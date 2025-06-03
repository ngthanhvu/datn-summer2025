<template>
  <ProductContent />
</template>

<script setup>
import ProductContent from '~/components/product/ProductContent.vue'

useHead({
  title: 'Sản phẩm - DEVGANG',
  meta: [
    { name: 'description', content: 'Sản phẩm - DEVGANG' },
  ],
})
</script>

<style scoped>
@media (max-width: 768px) {
  .tw-container {
    padding-bottom: 20px;
  }
}
</style>