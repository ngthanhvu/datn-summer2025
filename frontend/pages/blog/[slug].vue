<template>
    <div class="tw-max-w-4xl tw-mx-auto tw-px-4 sm:tw-px-6 tw-py-8 sm:tw-py-12">
        <!-- Breadcrumb -->
        <nav class="tw-flex tw-items-center tw-space-x-2 tw-text-sm tw-text-gray-500 tw-mb-8">
            <NuxtLink to="/" class="hover:tw-text-gray-700">Trang chủ</NuxtLink>
            <span>/</span>
            <NuxtLink to="/blogs" class="hover:tw-text-gray-700">Blog</NuxtLink>
            <span>/</span>
            <span class="tw-text-gray-900">{{ blog.title }}</span>
        </nav>

        <!-- Article Header -->
        <header class="tw-mb-8">
            <h1 class="tw-text-3xl sm:tw-text-4xl tw-font-bold tw-text-gray-900 tw-mb-4">
                {{ blog.title }}
            </h1>
            <div class="tw-flex tw-items-center tw-space-x-4 tw-text-sm tw-text-gray-500">
                <time :datetime="blog.date" class="tw-flex tw-items-center">
                    <i class="far fa-calendar-alt tw-mr-2"></i>
                    {{ formatDate(blog.date) }}
                </time>
                <span class="tw-flex tw-items-center">
                    <i class="far fa-clock tw-mr-2"></i>
                    {{ blog.readTime }} phút đọc
                </span>
                <span class="tw-flex tw-items-center">
                    <i class="far fa-eye tw-mr-2"></i>
                    {{ blog.views }} lượt xem
                </span>
            </div>
        </header>

        <!-- Featured Image -->
        <div class="tw-mb-8">
            <img :src="blog.image" :alt="blog.alt"
                class="tw-w-full tw-h-[400px] sm:tw-h-[500px] tw-object-cover tw-rounded-lg" />
        </div>

        <!-- Article Content -->
        <article class="tw-prose tw-prose-lg tw-max-w-none">
            <p class="tw-text-gray-600 tw-text-lg tw-leading-relaxed tw-mb-6">
                {{ blog.excerpt }}
            </p>

            <div v-html="blog.content" class="tw-space-y-6"></div>
        </article>

        <!-- Tags -->
        <div class="tw-mt-8 tw-pt-8 tw-border-t">
            <h3 class="tw-text-lg tw-font-semibold tw-mb-4">Tags:</h3>
            <div class="tw-flex tw-flex-wrap tw-gap-2">
                <span v-for="tag in blog.tags" :key="tag"
                    class="tw-px-3 tw-py-1 tw-bg-gray-100 tw-text-gray-700 tw-rounded-full tw-text-sm hover:tw-bg-gray-200 tw-cursor-pointer">
                    #{{ tag }}
                </span>
            </div>
        </div>

        <!-- Author Info -->
        <div class="tw-mt-8 tw-pt-8 tw-border-t">
            <div class="tw-flex tw-items-center tw-space-x-4">
                <img :src="blog.author.avatar" :alt="blog.author.name"
                    class="tw-w-16 tw-h-16 tw-rounded-full tw-object-cover" />
                <div>
                    <h3 class="tw-font-semibold tw-text-lg">{{ blog.author.name }}</h3>
                    <p class="tw-text-gray-600">{{ blog.author.bio }}</p>
                </div>
            </div>
        </div>

        <!-- Related Posts -->
        <div class="tw-mt-12 tw-pt-8 tw-border-t">
            <h2 class="tw-text-2xl tw-font-bold tw-mb-6">Bài viết liên quan</h2>
            <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 tw-gap-6">
                <article v-for="relatedPost in relatedPosts" :key="relatedPost.id" class="tw-group">
                    <NuxtLink :to="'/blogs/' + relatedPost.slug" class="tw-block">
                        <img :src="relatedPost.image" :alt="relatedPost.title"
                            class="tw-w-full tw-h-48 tw-object-cover tw-rounded-lg tw-mb-4 group-hover:tw-opacity-90 tw-transition-opacity" />
                        <h3 class="tw-font-semibold tw-text-lg tw-mb-2 group-hover:tw-text-primary">
                            {{ relatedPost.title }}
                        </h3>
                        <time class="tw-text-sm tw-text-gray-500">
                            {{ formatDate(relatedPost.date) }}
                        </time>
                    </NuxtLink>
                </article>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useError } from '#app'

const route = useRoute()
const error = useError()
const slug = route.params.slug

// Mock data - In real app, this would come from an API
const blogs = [
    {
        slug: '10-tips-for-capturing-stunning-landscape-photos',
        title: '10 Tips for Capturing Stunning Landscape Photos',
        date: '2023-05-25',
        readTime: 5,
        views: 1234,
        image: 'https://storage.googleapis.com/a1aa/image/0937c7ad-97ec-45f0-c69c-cdba4f61b684.jpg',
        alt: 'Landscape with rolling hills and trees under soft pink sky',
        excerpt: 'Landscape photography is one of the most popular genres among photographers. Whether you\'re a beginner or a seasoned pro, these tips will help you capture breathtaking landscape photos.',
        content: `
            <h2>1. Plan Your Shoot</h2>
            <p>Research your location beforehand. Check the weather forecast, sunrise/sunset times, and scout the area if possible. This preparation will help you capture the best possible shots.</p>
            
            <h2>2. Use the Right Equipment</h2>
            <p>A wide-angle lens is essential for landscape photography. Consider using a tripod for stability and filters to control light and enhance colors.</p>
            
            <h2>3. Master Composition</h2>
            <p>Use the rule of thirds, leading lines, and foreground interest to create compelling compositions. Don't forget to consider the horizon line placement.</p>
        `,
        tags: ['photography', 'landscape', 'tips', 'nature'],
        author: {
            name: 'John Doe',
            avatar: 'https://randomuser.me/api/portraits/men/1.jpg',
            bio: 'Professional photographer with 10+ years of experience in landscape and nature photography.'
        }
    },
    {
        slug: 'exploring-nature-photography',
        title: 'Exploring the Beauty of Nature Photography',
        date: '2024-01-15',
        readTime: 4,
        views: 856,
        image: 'https://storage.googleapis.com/a1aa/image/12fb8a47-07cd-4e18-ef1a-e5c8ca90c806.jpg',
        alt: 'Sunset over a calm lake with mountains in the background',
        excerpt: 'Discover tips and techniques to capture the stunning beauty of natural landscapes and wildlife.',
        content: `
            <h2>1. Understanding Natural Light</h2>
            <p>The key to great nature photography is understanding how to work with natural light. The golden hours of sunrise and sunset provide the most beautiful lighting conditions.</p>
            
            <h2>2. Composition in Nature</h2>
            <p>Learn how to frame your shots to create compelling compositions that draw the viewer's eye through the scene.</p>
        `,
        tags: ['photography', 'nature', 'landscape'],
        author: {
            name: 'Jane Smith',
            avatar: 'https://randomuser.me/api/portraits/women/1.jpg',
            bio: 'Nature photographer specializing in wildlife and landscape photography.'
        }
    }
]

// Find the blog post by slug
const blog = computed(() => {
    const foundBlog = blogs.find(b => b.slug === slug)
    if (!foundBlog) {
        throw createError({
            statusCode: 404,
            message: 'Bài viết không tồn tại'
        })
    }
    return foundBlog
})

// Get related posts (excluding current post)
const relatedPosts = computed(() => {
    return blogs
        .filter(b => b.slug !== slug)
        .slice(0, 2)
        .map(post => ({
            id: post.slug,
            title: post.title,
            date: post.date,
            image: post.image,
            slug: post.slug
        }))
})

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('vi-VN', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    })
}

// Define page meta
definePageMeta({
    layout: 'default'
})
</script>

<style scoped>
.tw-prose :deep(h2) {
    @apply tw-text-2xl tw-font-bold tw-mt-8 tw-mb-4 tw-text-gray-900;
}

.tw-prose :deep(p) {
    @apply tw-text-gray-600 tw-leading-relaxed tw-mb-4;
}

.tw-text-primary {
    color: #3bb77e;
}
</style>
