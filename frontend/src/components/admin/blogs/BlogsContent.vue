<template>
    <div class="brands-page">
        <div class="page-header flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1>Quản lý bài viết</h1>
                <p class="text-gray-600">Quản lý bài viết của bạn</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button @click="handleRefresh"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Tải lại
                </button>
                <router-link to="/admin/blogs/create"
                    class="w-full sm:w-auto bg-primary text-white rounded px-4 py-2 flex items-center justify-center gap-2 hover:bg-primary-dark cursor-pointer">
                    <i class="fas fa-plus"></i>
                    Thêm bài viết
                </router-link>
            </div>
        </div>
        <BlogTable :blogs="blogs" :loading="loading" :error="error" :pagination="pagination" @delete="deleteBlog"
            @refresh="handleRefresh" />
    </div>

</template>

<script setup>
import { onMounted } from 'vue'
import BlogTable from './BlogsTable.vue'
import { useBlog } from '../../../composable/useBlogs'

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

@media (max-width: 640px) {
    .page-header h1 {
        font-size: 1.5rem;
    }
}

.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
}
</style>