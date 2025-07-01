<template>
    <CategoryForm />
</template>

<script setup>
useHead({
    title: 'Thêm danh mục'
})
definePageMeta({
    layout: 'admin',
    middleware: 'admin',
})
import CategoryForm from '~/components/admin/categories/CategoryForm.vue'
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}
</style>