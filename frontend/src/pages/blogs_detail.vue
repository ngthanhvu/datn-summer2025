<template>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Skeleton Loading -->
        <div v-if="loading" class="flex flex-col lg:flex-row gap-8 animate-pulse">
            <!-- Skeleton cho phần nội dung chính -->
            <div class="flex-1 min-w-0 bg-white p-8">
                <div class="h-6 bg-gray-200 rounded w-1/3 mb-4"></div>
                <div class="h-8 bg-gray-300 rounded w-2/3 mb-6"></div>
                <div class="flex gap-4 mb-4">
                    <div class="h-4 bg-gray-200 rounded w-20"></div>
                    <div class="h-4 bg-gray-200 rounded w-24"></div>
                    <div class="h-4 bg-gray-200 rounded w-16"></div>
                </div>
                <div class="h-64 bg-gray-200 rounded mb-6"></div>
                <div class="space-y-3">
                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    <div class="h-4 bg-gray-200 rounded w-4/6"></div>
                </div>
            </div>

            <!-- Skeleton cho Sidebar -->
            <aside class="w-full lg:w-80 flex-shrink-0 space-y-6">
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="h-5 bg-gray-200 rounded w-1/2 mb-4"></div>
                    <div class="space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 shadow-sm">
                    <div class="h-5 bg-gray-200 rounded w-1/2 mb-4"></div>
                    <div class="space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    </div>
                </div>
            </aside>
        </div>

        <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ error }}
        </div>
        <div v-else-if="blog">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Main Content -->
                <div class="flex-1 min-w-0 bg-white p-8">
                    <nav class="flex items-center flex-wrap gap-2 text-sm text-gray-600">
                        <router-link to="/" class="hover:text-primary hover:underline">Trang chủ</router-link>
                        <span>/</span>
                        <router-link to="/tin-tuc" class="hover:text-primary hover:underline">Bài viết</router-link>
                        <span>/</span>
                        <span class="font-medium text-gray-800">{{ blog.title }}</span>
                    </nav>
                    <div class="space-y-4">
                        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold leading-tight">{{ blog.title }}</h1>
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user text-primary"></i>
                                <span>{{ blog.author?.username || blog.author?.name || 'Unknown' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-calendar text-primary"></i>
                                <span>{{ formatDate(blog.published_at || blog.created_at) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-eye text-primary"></i>
                                <span>{{ blog.view_count || 0 }} lượt xem</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="blog.image" class="flex justify-center my-6">
                        <img :src="blog.image" :alt="blog.title" class="blog-image" />
                    </div>
                    <div v-if="blog.categories?.length || blog.tags?.length" class="flex flex-wrap gap-2">
                        <router-link v-for="category in blog.categories" :key="category.id"
                            :to="`/blogs/category/${category.slug}`"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm">
                            {{ category.name }}
                        </router-link>
                        <span v-for="tag in blog.tags" :key="tag"
                            class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded-full text-sm">
                            #{{ tag }}
                        </span>
                    </div>
                    <article class="prose max-w-none w-full">
                        <div class="editor-content" v-html="blog.content"></div>
                    </article>
                    <div class="flex items-center gap-4 pt-4 border-t border-gray-300">
                        <span class="text-gray-600">Chia sẻ:</span>
                        <a v-for="social in socialPlatforms" :key="social.name" :href="getShareUrl(social)"
                            target="_blank" class="text-gray-500 hover:text-primary text-xl"
                            :title="'Share on ' + social.name">
                            <i :class="social.icon"></i>
                        </a>
                    </div>
                    <div v-if="blog.author" class="bg-gray-50 rounded-xl p-6 flex flex-col md:flex-row gap-2 mt-8">
                        <div class="flex-shrink-0">
                            <img :src="blog.author.avatar || '/https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'"
                                :alt="blog.author.username || blog.author.name"
                                class="w-10 h-10 rounded-full object-cover" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold">{{ blog.author.username || blog.author.name }}</h3>
                            <p v-if="blog.author.bio" class="text-gray-600 mt-2">{{ blog.author.bio }}</p>
                            <div v-if="blog.author.social_links" class="flex gap-3 mt-3">
                                <a v-for="(link, platform) in blog.author.social_links" :key="platform" :href="link"
                                    target="_blank" class="text-gray-500 hover:text-primary">
                                    <i :class="getSocialIcon(platform)"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sidebar -->
                <aside class="w-full lg:w-80 flex-shrink-0">
                    <div class="bg-white rounded-xl p-6 shadow-sm mb-6">
                        <h2 class="text-lg font-semibold mb-4">Bài viết liên quan</h2>
                        <div class="text-gray-500 text-sm">(Đang cập nhật...)</div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h2 class="text-lg font-semibold mb-4">Danh mục</h2>
                        <div class="text-gray-500 text-sm">(Đang cập nhật...)</div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, watch } from 'vue'
import { useHead } from '@vueuse/head'
import { useBlog } from '../composable/useBlogs'
import { useAuth } from '../composable/useAuth'
import { useRouter, useRoute } from 'vue-router'

const { isAuthenticated } = useAuth()
const router = useRouter()
const route = useRoute()
const { blog, loading, error, fetchBlogBySlug } = useBlog()

const socialPlatforms = [
    { name: 'Facebook', icon: 'fab fa-facebook', url: 'https://www.facebook.com/sharer/sharer.php?u=' },
    { name: 'Twitter', icon: 'fab fa-twitter', url: 'https://twitter.com/intent/tweet?text=' },
    { name: 'LinkedIn', icon: 'fab fa-linkedin', url: 'https://www.linkedin.com/shareArticle?mini=true&url=' },
    { name: 'Zalo', icon: 'fab fa-zalo', url: 'https://zalo.me/share?text=' }
]

onMounted(async () => {
    try {
        await fetchBlogBySlug(route.params.slug)
        if (!blog.value) {
            error.value = 'Blog not found'
            return
        }
    } catch (err) {
        error.value = 'Blog not found'
        console.log(err)
        blog.value = null
    }
})

watch(
    () => blog.value,
    (val) => {
        if (val) {
            useHead({
                title: val.title,
                meta: [
                    {
                        name: 'description',
                        content: val.description || val.title || 'Bài viết trên blog'
                    },
                    {
                        property: 'og:title',
                        content: val.title
                    },
                    {
                        property: 'og:description',
                        content: val.description || val.title
                    },
                    {
                        property: 'og:image',
                        content: val.image || '/default-og.jpg'
                    },
                    {
                        property: 'og:url',
                        content: window.location.href
                    }
                ]
            })
        }
    },
    { immediate: true }
)


const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('vi-VN', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    })
}

const getShareUrl = (social) => {
    const url = window.location.href
    const title = blog.value?.title || ''
    return `${social.url}${encodeURIComponent(url)}&title=${encodeURIComponent(title)}`
}

const getSocialIcon = (platform) => {
    const icons = {
        facebook: 'fab fa-facebook',
        twitter: 'fab fa-twitter',
        linkedin: 'fab fa-linkedin',
        youtube: 'fab fa-youtube',
        instagram: 'fab fa-instagram'
    }
    return icons[platform.toLowerCase()] || 'fas fa-link'
}
</script>

<style>
.blog-image {
    max-width: 100%;
    max-height: 350px;
    width: auto;
    height: auto;
    object-fit: cover;
    border-radius: 1rem;
    /* Bỏ shadow */
    box-shadow: none;
    background: #f8f8f8;
}

@media (max-width: 1023px) {
    aside {
        margin-top: 2rem;
        width: 100% !important;
        position: static !important;
    }
}

aside {
    position: sticky;
    top: 2rem;
    align-self: flex-start;
    height: fit-content;
}

/* Phong cách cơ bản cho nội dung CKEditor */
.editor-content {
    font-family: Arial, Helvetica, sans-serif;
    color: #333;
    line-height: 1.6;
}

/* Tiêu đề */
.editor-content h1 {
    font-size: 2.25rem;
    font-weight: bold;
    margin: 1.2em 0 0.6em;
}

.editor-content h2 {
    font-size: 1.75rem;
    font-weight: bold;
    margin: 1.1em 0 0.55em;
}

.editor-content h3 {
    font-size: 1.5rem;
    font-weight: bold;
    margin: 1em 0 0.5em;
}

.editor-content h4 {
    font-size: 1.25rem;
    font-weight: bold;
    margin: 0.9em 0 0.45em;
}

.editor-content h5 {
    font-size: 1.125rem;
    font-weight: bold;
    margin: 0.8em 0 0.4em;
}

.editor-content h6 {
    font-size: 1rem;
    font-weight: bold;
    margin: 0.7em 0 0.35em;
}

/* Đoạn văn */
.editor-content p {
    margin-bottom: 1em;
}

/* Danh sách */
.editor-content ul {
    padding-left: 1.5em;
    margin-bottom: 1em;
}

.editor-content ol {
    padding-left: 1.5em;
    margin-bottom: 1em;
}

.editor-content li {
    margin-bottom: 0.5em;
}

/* Blockquote */
.editor-content blockquote {
    border-left: 4px solid #ccc;
    padding-left: 1em;
    color: #555;
    font-style: italic;
    margin: 1em 0;
}

/* Bảng - CSS mạnh hơn để đảm bảo hiển thị */
.editor-content table,
.editor-content figure.table table,
.editor-content table *,
.editor-content figure.table table * {
    border-collapse: collapse !important;
    border-spacing: 0 !important;
}

.editor-content table,
.editor-content figure.table table {
    width: 100% !important;
    margin-bottom: 1em !important;
    border: 2px solid #e5e7eb !important;
    border-radius: 0.5rem !important;
    overflow: hidden !important;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1) !important;
    background-color: #ffffff !important;
}

.editor-content th,
.editor-content td,
.editor-content figure.table th,
.editor-content figure.table td {
    border: 1px solid #e5e7eb !important;
    padding: 0.75em !important;
    text-align: left !important;
    vertical-align: top !important;
    min-width: 100px !important;
}

.editor-content th,
.editor-content figure.table th {
    background-color: #f9fafb !important;
    font-weight: 600 !important;
    color: #374151 !important;
    border-bottom: 2px solid #e5e7eb !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important;
}

.editor-content td,
.editor-content figure.table td {
    background-color: #ffffff !important;
    border: 1px solid #e5e7eb !important;
}

.editor-content tr:nth-child(even) td,
.editor-content figure.table tr:nth-child(even) td {
    background-color: #f9fafb !important;
}

.editor-content tr:hover td,
.editor-content figure.table tr:hover td {
    background-color: #f3f4f6 !important;
}

/* Responsive table */
.editor-content table,
.editor-content figure.table table {
    display: block !important;
    overflow-x: auto !important;
    white-space: nowrap !important;
    max-width: 100% !important;
}

@media (min-width: 768px) {

    .editor-content table,
    .editor-content figure.table table {
        display: table !important;
        white-space: normal !important;
    }
}

/* Đảm bảo tất cả table elements đều có border */
.editor-content table,
.editor-content table tr,
.editor-content table th,
.editor-content table td,
.editor-content figure.table table,
.editor-content figure.table table tr,
.editor-content figure.table table th,
.editor-content figure.table table td {
    border: 1px solid #e5e7eb !important;
}

/* Đảm bảo figure.table không có margin/padding không mong muốn */
.editor-content figure.table {
    margin: 1em 0 !important;
    padding: 0 !important;
    border: none !important;
    background: none !important;
}

/* Hình ảnh */
.editor-content img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 1em 0;
}

/* Link */
.editor-content a {
    color: #3bb77e;
    text-decoration: underline;
}

.editor-content a:hover {
    color: #2ea16d;
}

/* Media Embed (YouTube, Vimeo...) */
.editor-content .media {
    margin: 1em 0;
}

.editor-content iframe {
    width: 100%;
    max-width: 100%;
    height: auto;
    aspect-ratio: 16 / 9;
    border: none;
}

.editor-content figure.image {
    display: table;
    margin: 1em auto;
    text-align: center;
}

.editor-content figure.image img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
}

.editor-content figure.image figcaption {
    margin-top: 0.5em;
    color: #777;
    font-size: 0.875rem;
    font-style: italic;
    text-align: center;
}
</style>
