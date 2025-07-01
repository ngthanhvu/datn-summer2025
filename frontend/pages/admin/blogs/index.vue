<template>
    <div class="brands-page">
        <div class="page-header tw-flex tw-justify-between tw-items-center">
            <div>
                <h1>Quản lý bài viết</h1>
                <p class="text-gray-600">Quản lý bài viết của bạn</p>
            </div>
            <NuxtLink to="/admin/blogs/create"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-primary-dark">
                <i class="fas fa-plus"></i>
                Thêm bài viết
            </NuxtLink>
        </div>
        <BlogTable />
    </div>

</template>

<script setup>
definePageMeta({
    layout: 'admin',
    middleware: 'admin'
})
useHead({
    title: "Quản lý bài viết"
})
import BlogTable from '@/components/admin/blogs/BlogTable.vue';
</script>

<style>
.brands-page {
    padding: 1.5rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.875rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}
</style>