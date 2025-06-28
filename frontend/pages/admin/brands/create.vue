<template>
    <BrandForm />
</template>

<script setup>
useHead({
    title: 'Thêm thương hiệu'
})
definePageMeta({
    layout: 'admin',
    middleware: 'admin',
})
import BrandForm from '~/components/admin/brands/BrandForm.vue'
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}
</style>