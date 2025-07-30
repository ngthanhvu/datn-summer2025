<template>
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold mb-4">Tin tức mới nhất</h1>
            <p class="text-lg text-gray-600">Cập nhật những bài viết và kiến thức mới nhất</p>
        </div>

        <div v-if="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        </div>

        <div v-else>
            <div v-if="filteredBlogs.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-8">
                <div v-for="blog in filteredBlogs" :key="blog.id"
                    class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <RouterLink :to="`/blog/${blog.slug}`" class="no-underline text-gray-900">
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
