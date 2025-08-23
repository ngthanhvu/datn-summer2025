<template>
    <div class="max-w-6xl mx-auto px-4 py-4 sm:py-8">
        <!-- Skeleton Loading -->
        <div v-if="loading" class="flex flex-col lg:flex-row gap-6 lg:gap-8 animate-pulse">
            <!-- Skeleton cho phần nội dung chính -->
            <div class="flex-1 min-w-0 bg-white p-4 sm:p-8 rounded-lg">
                <div class="h-6 bg-gray-200 rounded w-1/3 mb-4"></div>
                <div class="h-8 bg-gray-300 rounded w-2/3 mb-6"></div>
                <div class="flex gap-4 mb-4">
                    <div class="h-4 bg-gray-200 rounded w-20"></div>
                    <div class="h-4 bg-gray-200 rounded w-24"></div>
                    <div class="h-4 bg-gray-200 rounded w-16"></div>
                </div>
                <div class="h-48 sm:h-64 bg-gray-200 rounded mb-6"></div>
                <div class="space-y-3">
                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    <div class="h-4 bg-gray-200 rounded w-4/6"></div>
                </div>
            </div>

            <!-- Skeleton cho Sidebar -->
            <aside class="w-full lg:w-80 flex-shrink-0 space-y-4 lg:space-y-6">
                <div class="bg-white rounded-lg p-4 sm:p-6 shadow-sm">
                    <div class="h-5 bg-gray-200 rounded w-1/2 mb-4"></div>
                    <div class="space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-4 sm:p-6 shadow-sm">
                    <div class="h-5 bg-gray-200 rounded w-1/2 mb-4"></div>
                    <div class="space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                    </div>
                </div>
            </aside>
        </div>

        <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ error }}
        </div>
        <div v-else-if="blog">
            <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
                <!-- Main Content -->
                <div class="flex-1 min-w-0 bg-white p-4 sm:p-6 lg:p-8 rounded-lg shadow-sm">
                    <nav class="flex items-center flex-wrap gap-1 sm:gap-2 text-xs sm:text-sm text-gray-600 mb-4">
                        <router-link to="/" class="hover:text-primary hover:underline">Trang chủ</router-link>
                        <span>/</span>
                        <router-link to="/tin-tuc" class="hover:text-primary hover:underline">Bài viết</router-link>
                        <span>/</span>
                        <span class="font-medium text-gray-800 truncate">{{ blog.title }}</span>
                    </nav>
                    <div class="space-y-3 sm:space-y-4">
                        <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold leading-tight">{{
                            blog.title }}</h1>
                        <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs sm:text-sm text-gray-500">
                            <div class="flex items-center gap-1 sm:gap-2">
                                <i class="fas fa-user text-primary"></i>
                                <span class="truncate">{{ blog.author?.username || blog.author?.name || 'Unknown'
                                }}</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-2">
                                <i class="fas fa-calendar text-primary"></i>
                                <span>{{ formatDate(blog.published_at || blog.created_at) }}</span>
                            </div>
                            <div class="flex items-center gap-1 sm:gap-2">
                                <i class="fas fa-eye text-primary"></i>
                                <span>{{ blog.view_count || 0 }} lượt xem</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="blog.image" class="flex justify-center my-4 sm:my-6">
                        <img :src="blog.image" :alt="blog.title" class="blog-image" />
                    </div>

                    <!-- Category display -->
                    <div v-if="blog.category" class="flex flex-wrap gap-2 my-4">
                        <button
                            class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded-full text-sm font-medium transition-colors">
                            <i class="fas fa-folder mr-2"></i>
                            {{ blog.category.name }}
                        </button>
                    </div>

                    <article class="prose max-w-none w-full">
                        <div class="editor-content" v-html="blog.content"></div>
                    </article>
                    <div class="flex items-center gap-3 sm:gap-4 pt-4 border-t border-gray-300 mt-6">
                        <span class="text-gray-600 text-sm">Chia sẻ:</span>
                        <a v-for="social in socialPlatforms" :key="social.name" :href="getShareUrl(social)"
                            target="_blank" class="text-gray-500 hover:text-primary text-lg sm:text-xl"
                            :title="'Share on ' + social.name">
                            <i :class="social.icon"></i>
                        </a>
                    </div>
                    <div v-if="blog.author"
                        class="bg-gray-50 rounded-lg p-4 sm:p-6 flex flex-col sm:flex-row gap-3 sm:gap-4 mt-6 sm:mt-8">
                        <div class="flex-shrink-0">
                            <img :src="blog.author.avatar || 'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'"
                                :alt="blog.author.username || blog.author.name"
                                class="w-12 h-12 sm:w-16 sm:h-16 rounded-full object-cover" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm sm:text-base font-semibold">{{ blog.author.username || blog.author.name
                            }}</h3>
                            <p v-if="blog.author.bio" class="text-gray-600 mt-2 text-sm">{{ blog.author.bio }}</p>
                            <div v-if="blog.author.social_links" class="flex gap-3 mt-3">
                                <a v-for="(link, platform) in blog.author.social_links" :key="platform" :href="link"
                                    target="_blank" class="text-gray-500 hover:text-primary text-lg">
                                    <i :class="getSocialIcon(platform)"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <aside class="w-full lg:w-80 flex-shrink-0">
                    <!-- Related Blogs -->
                    <div class="bg-white rounded-lg p-4 sm:p-6 shadow-sm mb-4 lg:mb-6">
                        <h2 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4">Bài viết liên quan</h2>
                        <div v-if="relatedBlogsLoading" class="space-y-3">
                            <div v-for="n in 3" :key="n" class="flex gap-3">
                                <div class="w-16 h-12 bg-gray-200 rounded animate-pulse"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="h-3 bg-gray-200 rounded w-full animate-pulse"></div>
                                    <div class="h-3 bg-gray-200 rounded w-2/3 animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="!relatedBlogs || relatedBlogs.length === 0" class="text-gray-500 text-sm">
                            Không có bài viết liên quan
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="relatedBlog in relatedBlogs" :key="relatedBlog.id" class="flex gap-3 group">
                                <div class="flex-shrink-0">
                                    <img v-if="relatedBlog.image" :src="getImageUrl(relatedBlog.image)"
                                        class="w-16 h-12 object-cover rounded" :alt="relatedBlog.title" />
                                    <div v-else class="w-16 h-12 bg-gray-200 rounded flex items-center justify-center">
                                        <i class="fas fa-newspaper text-gray-400"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <router-link :to="`/tin-tuc/${relatedBlog.slug}`"
                                        class="block text-sm font-medium text-gray-800 group-hover:text-primary line-clamp-2">
                                        {{ relatedBlog.title }}
                                    </router-link>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ formatDate(relatedBlog.published_at || relatedBlog.created_at) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="bg-white rounded-lg p-4 sm:p-6 shadow-sm">
                        <h2 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4">Danh mục</h2>
                        <div v-if="categoriesLoading" class="space-y-2">
                            <div v-for="n in 5" :key="n" class="h-4 bg-gray-200 rounded animate-pulse"></div>
                        </div>
                        <div v-else-if="!categories || categories.length === 0" class="text-gray-500 text-sm">
                            Chưa có danh mục nào
                        </div>
                        <div v-else class="space-y-2">
                            <button v-for="category in categories" :key="category.id"
                                class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 transition-colors group">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-folder text-blue-500 group-hover:text-blue-600"></i>
                                    <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ category.name
                                    }}</span>
                                </div>
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                                    {{ category.blogs_count || 0 }}
                                </span>
                            </button>
                        </div>
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
const { blog, loading, error, categories, categoriesLoading, relatedBlogs, relatedBlogsLoading, fetchBlogBySlug, fetchCategories, fetchRelatedBlogs, increaseView } = useBlog()

const socialPlatforms = [
    { name: 'Facebook', icon: 'fab fa-facebook', url: 'https://www.facebook.com/sharer/sharer.php?u=' },
    { name: 'Twitter', icon: 'fab fa-twitter', url: 'https://twitter.com/intent/tweet?text=' },
    { name: 'LinkedIn', icon: 'fab fa-linkedin', url: 'https://www.linkedin.com/shareArticle?mini=true&url=' },
    { name: 'Zalo', icon: 'fab fa-zalo', url: 'https://zalo.me/share?text=' }
]

onMounted(async () => {
    try {
        await Promise.all([
            fetchBlogBySlug(route.params.slug),
            fetchCategories()
        ])

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
    async (val) => {
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

            // Fetch related blogs if category exists
            if (val.category?.id) {
                try {
                    await fetchRelatedBlogs(val.category.id, val.id, 5)
                } catch (err) {
                    console.error('Failed to fetch related blogs:', err)
                }
            }
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

const getImageUrl = (path) => {
    if (!path) return ''
    return path.startsWith('/storage/') ? `http://localhost:8000${path}` : path
}
</script>

<style>
@media (max-width: 1023px) {
    aside {
        margin-top: 1.5rem;
        width: 100% !important;
        position: static !important;
    }
}

@media (min-width: 1024px) {
    aside {
        position: sticky;
        top: 2rem;
        align-self: flex-start;
        height: fit-content;
    }
}

/* Phong cách cơ bản cho nội dung CKEditor */
.editor-content {
    font-family: Arial, Helvetica, sans-serif;
    color: #333;
    line-height: 1.6;
    font-size: 0.875rem;
}

@media (min-width: 640px) {
    .editor-content {
        font-size: 1rem;
    }
}

/* Tiêu đề */
.editor-content h1 {
    font-size: 1.5rem;
    font-weight: bold;
    margin: 1.2em 0 0.6em;
}

.editor-content h2 {
    font-size: 1.25rem;
    font-weight: bold;
    margin: 1.1em 0 0.55em;
}

.editor-content h3 {
    font-size: 1.125rem;
    font-weight: bold;
    margin: 1em 0 0.5em;
}

.editor-content h4 {
    font-size: 1rem;
    font-weight: bold;
    margin: 0.9em 0 0.45em;
}

.editor-content h5 {
    font-size: 0.875rem;
    font-weight: bold;
    margin: 0.8em 0 0.4em;
}

.editor-content h6 {
    font-size: 0.75rem;
    font-weight: bold;
    margin: 0.7em 0 0.35em;
}

@media (min-width: 640px) {
    .editor-content h1 {
        font-size: 2.25rem;
    }

    .editor-content h2 {
        font-size: 1.75rem;
    }

    .editor-content h3 {
        font-size: 1.5rem;
    }

    .editor-content h4 {
        font-size: 1.25rem;
    }

    .editor-content h5 {
        font-size: 1.125rem;
    }

    .editor-content h6 {
        font-size: 1rem;
    }
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
    min-width: 100% !important;
    max-width: none !important;
}

/* Container cho table để đảm bảo scroll */
.editor-content table,
.editor-content figure.table {
    display: block !important;
    width: 100% !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    -webkit-overflow-scrolling: touch !important;
    scrollbar-width: thin !important;
    scrollbar-color: #cbd5e0 #f7fafc !important;
}

/* Custom scrollbar cho webkit browsers */
.editor-content table::-webkit-scrollbar,
.editor-content figure.table::-webkit-scrollbar {
    height: 6px !important;
}

.editor-content table::-webkit-scrollbar-track,
.editor-content figure.table::-webkit-scrollbar-track {
    background: #f7fafc !important;
    border-radius: 3px !important;
}

.editor-content table::-webkit-scrollbar-thumb,
.editor-content figure.table::-webkit-scrollbar-thumb {
    background: #cbd5e0 !important;
    border-radius: 3px !important;
}

.editor-content table::-webkit-scrollbar-thumb:hover,
.editor-content figure.table::-webkit-scrollbar-thumb:hover {
    background: #a0aec0 !important;
}

/* Đảm bảo table content không bị wrap */
.editor-content table,
.editor-content figure.table table {
    white-space: nowrap !important;
    min-width: max-content !important;
}

.editor-content th,
.editor-content td,
.editor-content figure.table th,
.editor-content figure.table td {
    border: 1px solid #e5e7eb !important;
    padding: 0.5em !important;
    text-align: left !important;
    vertical-align: top !important;
    min-width: 80px !important;
    font-size: 0.75rem !important;
    white-space: nowrap !important;
    word-wrap: normal !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

@media (min-width: 640px) {

    .editor-content th,
    .editor-content td,
    .editor-content figure.table th,
    .editor-content figure.table td {
        padding: 0.75em !important;
        min-width: 100px !important;
        font-size: 0.875rem !important;
    }
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
    width: 100% !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
}

/* Responsive table - chỉ áp dụng trên desktop lớn */
@media (min-width: 1024px) {

    .editor-content table,
    .editor-content figure.table table {
        white-space: normal !important;
        min-width: 100% !important;
    }

    .editor-content th,
    .editor-content td,
    .editor-content figure.table th,
    .editor-content figure.table td {
        white-space: normal !important;
        word-wrap: break-word !important;
    }
}

/* Hình ảnh */
.editor-content img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 1em 0;
    border-radius: 0.5rem;
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
    border-radius: 0.5rem;
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
    font-size: 0.75rem;
    font-style: italic;
    text-align: center;
}

@media (min-width: 640px) {
    .editor-content figure.image figcaption {
        font-size: 0.875rem;
    }
}
</style>
