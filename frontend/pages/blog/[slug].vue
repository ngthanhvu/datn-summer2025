<template>
    <div class="tw-max-w-4xl tw-mx-auto tw-p-4">
        <div v-if="loading" class="tw-text-center tw-py-8">
            <div class="tw-inline-block tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-b-2 tw-border-blue-500"></div>
        </div>

        <div v-else-if="error" class="tw-bg-red-100 tw-border tw-border-red-400 tw-text-red-700 tw-px-4 tw-py-3 tw-rounded">
            {{ error }}
        </div>

        <div v-else-if="blog" class="tw-space-y-6">
            <!-- Breadcrumb -->
            <nav class="tw-flex tw-items-center tw-space-x-2 tw-text-sm">
                <NuxtLink to="/" class="hover:tw-underline">Trang chủ</NuxtLink>
                <span>/</span>
                <NuxtLink to="/blogs" class="hover:tw-underline">Blog</NuxtLink>
                <span>/</span>
                <span class="tw-font-medium">{{ blog.title }}</span>
            </nav>

            <!-- Nội dung blog -->
            <article>
                <h1 class="tw-text-3xl tw-font-bold tw-mb-4">{{ blog.title }}</h1>
                
                <div class="tw-flex tw-items-center tw-space-x-4 tw-text-sm tw-text-gray-500 tw-mb-6">
                    <span>Tác giả: {{ blog.author?.name || 'Unknown' }}</span>
                    <span>Ngày đăng: {{ formatDate(blog.published_at || blog.created_at) }}</span>
                </div>

                <div class="tw-prose tw-max-w-none" v-html="blog.content"></div>
            </article>
        </div>
    </div>
</template>

<script setup>
import { useBlog } from '~/composables/useBlog'

const route = useRoute()
const { blog, loading, error, fetchBlog } = useBlog()

onMounted(async () => {
    await fetchBlog(route.params.id)
})

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