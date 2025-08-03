<template>
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold mb-4">Tin tức mới nhất</h1>
            <p class="text-lg text-gray-600">Cập nhật những bài viết và kiến thức mới nhất</p>
        </div>

        <!-- Skeleton Loading -->
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
            <div v-for="i in 3" :key="i" class="bg-white rounded-lg shadow-md overflow-hidden animate-pulse">
                <div class="h-48 bg-gray-200"></div>
                <div class="p-6">
                    <div class="h-4 bg-gray-200 rounded w-1/3 mb-3"></div>
                    <div class="h-6 bg-gray-300 rounded w-3/4 mb-3"></div>
                    <div class="h-4 bg-gray-200 rounded w-full mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6 mb-4"></div>
                    <div class="h-4 bg-gray-300 rounded w-20"></div>
                </div>
            </div>
        </div>

        <div v-else>
            <div v-if="filteredBlogs.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
                <div v-for="blog in filteredBlogs" :key="blog.id"
                    class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <RouterLink :to="`/tin-tuc/${blog.slug}`" class="no-underline text-gray-900">
                        <div class="relative h-48 overflow-hidden">
                            <img v-if="blog.image" :src="blog.image" :alt="blog.title"
                                class="w-full h-full object-cover" />
                            <div v-else class="bg-gray-200 w-full h-full flex items-center justify-center">
                                <span class="text-gray-500">Không có hình ảnh</span>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <span>{{ formatDate(blog.published_at || blog.created_at) }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ blog.author?.username || 'Unknown' }}</span>
                            </div>
                            <h3 class="text-xl font-semibold mb-2 line-clamp-2">{{ blog.title }}</h3>
                            <p class="text-gray-600 mb-4 line-clamp-3">{{ blog.description }}</p>
                            <div class="text-[#81aacc] font-medium hover:underline">Đọc thêm</div>
                        </div>
                    </RouterLink>
                </div>
            </div>

            <div v-else class="text-center text-gray-500 py-12">
                Không có bài viết nào.
            </div>

            <div v-if="pagination && filteredBlogs.length > 0" class="flex justify-center mt-8">
                <button v-for="page in pagination.last_page" :key="page" @click="fetchBlogs(page)" :class="{
                    'bg-[#81aacc] text-white': page === pagination.current_page,
                    'bg-white text-gray-700': page !== pagination.current_page
                }" class="px-4 py-2 mx-1 rounded border border-gray-300 hover:bg-[#4a8abe]">
                    {{ page }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useBlogStore } from '../../stores/blogs'
import { RouterLink } from 'vue-router'

const blogStore = useBlogStore()

onMounted(() => {
    blogStore.getBlogs()
})

const blogs = computed(() => blogStore.blogs)
const loading = computed(() => blogStore.loading)
const pagination = computed(() => blogStore.pagination)

const filteredBlogs = computed(() =>
    blogs.value ? blogs.value.filter(blog => blog.status === 'published') : []
)

const fetchBlogs = (page) => {
    blogStore.getBlogs(page)
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('vi-VN', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    })
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    line-clamp: 3;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
