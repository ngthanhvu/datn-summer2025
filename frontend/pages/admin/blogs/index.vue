<template>
    <div class="brands-page">
        <div class="page-header tw-flex tw-justify-between tw-items-center">
            <div>
                <h1>Quản lý bài viết</h1>
                <p class="text-gray-600">Quản lý bài viết của bạn</p>
            </div>
            <div class="tw-flex tw-gap-3">
                <button @click="handleRefresh"
                    class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-gray-600 tw-text-white tw-text-sm tw-font-medium tw-rounded-lg hover:tw-bg-gray-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-gray-500 focus:tw-ring-offset-2 tw-transition-colors tw-duration-200">
                    <svg class="tw-w-4 tw-h-4 tw-mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Tải lại
                </button>
                <NuxtLink to="/admin/blogs/create"
                    class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-primary-dark">
                    <i class="fas fa-plus"></i>
                    Thêm bài viết
                </NuxtLink>
            </div>
        </div>
        <BlogTable :blogs="blogs" :loading="loading" :error="error" :pagination="pagination" @delete="deleteBlog"
            @refresh="handleRefresh" />
    </div>

</template>

<script setup>
import { ref, onMounted } from 'vue'
import BlogTable from '@/components/admin/blogs/BlogTable.vue'
import { useBlog } from '@/composables/useBlog'

definePageMeta({
    layout: 'admin',
    middleware: 'admin'
})
useHead({
    title: "Quản lý bài viết"
})

const { blogs, loading, error, pagination, fetchBlogs, deleteBlog } = useBlog()

const handleRefresh = async () => {
    await fetchBlogs()
}

onMounted(fetchBlogs)
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