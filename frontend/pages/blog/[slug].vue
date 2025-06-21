<template>
  <div class="tw-max-w-6xl tw-mx-auto tw-px-4 tw-py-8">
    <div v-if="loading" class="tw-text-center tw-py-12">
      <div class="tw-inline-block tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-b-2 tw-border-blue-500"></div>
    </div>
    <div v-else-if="error"
      class="tw-bg-red-100 tw-border tw-border-red-400 tw-text-red-700 tw-px-4 tw-py-3 tw-rounded tw-mb-6">
      {{ error }}
    </div>
    <div v-else-if="blog" class="tw-space-y-8">
      <nav class="tw-flex tw-items-center tw-flex-wrap tw-gap-2 tw-text-sm tw-text-gray-600">
        <NuxtLink to="/" class="hover:tw-text-primary hover:tw-underline">Trang chủ</NuxtLink>
        <span>/</span>
        <NuxtLink to="/blogs" class="hover:tw-text-primary hover:tw-underline">Blog</NuxtLink>
        <span>/</span>
        <span class="tw-font-medium tw-text-gray-800">{{ blog.title }}</span>
      </nav>
      <div class="tw-space-y-4">
        <h1 class="tw-text-3xl md:tw-text-4xl lg:tw-text-5xl tw-font-bold tw-leading-tight">{{ blog.title }}</h1>
        <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-4 tw-text-sm tw-text-gray-500">
          <div class="tw-flex tw-items-center tw-gap-2">
            <i class="fas fa-user tw-text-primary"></i>
            <span>{{ blog.author?.username || blog.author?.name || 'Unknown' }}</span>
          </div>
          <div class="tw-flex tw-items-center tw-gap-2">
            <i class="fas fa-calendar tw-text-primary"></i>
            <span>{{ formatDate(blog.published_at || blog.created_at) }}</span>
          </div>
          <div class="tw-flex tw-items-center tw-gap-2">
            <i class="fas fa-eye tw-text-primary"></i>
            <span>{{ blog.view_count || 0 }} lượt xem</span>
          </div>
        </div>
      </div>
      <div v-if="blog.image" class="tw-rounded-xl tw-overflow-hidden tw-shadow-lg">
        <img :src="blog.image" :alt="blog.title" class="tw-w-full tw-h-auto tw-object-cover" />
      </div>
      <div v-if="blog.categories?.length || blog.tags?.length" class="tw-flex tw-flex-wrap tw-gap-2">
        <NuxtLink v-for="category in blog.categories" :key="category.id" :to="`/blogs/category/${category.slug}`"
          class="tw-bg-gray-100 hover:tw-bg-gray-200 tw-text-gray-800 tw-px-3 tw-py-1 tw-rounded-full tw-text-sm">
          {{ category.name }}
        </NuxtLink>
        <span v-for="tag in blog.tags" :key="tag"
          class="tw-bg-blue-100 hover:tw-bg-blue-200 tw-text-blue-800 tw-px-3 tw-py-1 tw-rounded-full tw-text-sm">
          #{{ tag }}
        </span>
      </div>
      <article class="tw-prose tw-max-w-none tw-w-full">
        <div v-html="blog.content"></div>
      </article>
      <div class="tw-flex tw-items-center tw-gap-4 tw-pt-4 tw-border-t">
        <span class="tw-text-gray-600">Chia sẻ:</span>
        <a v-for="social in socialPlatforms" :key="social.name" :href="getShareUrl(social)" target="_blank"
          class="tw-text-gray-500 hover:tw-text-primary tw-text-xl" :title="'Share on ' + social.name">
          <i :class="social.icon"></i>
        </a>
      </div>
      <div v-if="blog.author" class="tw-bg-gray-50 tw-rounded-xl tw-p-6 tw-flex tw-flex-col md:tw-flex-row tw-gap-6">
        <div class="tw-flex-shrink-0">
          <img :src="blog.author.avatar || '/images/default-avatar.png'" :alt="blog.author.username || blog.author.name"
            class="tw-w-20 tw-h-20 tw-rounded-full tw-object-cover" />
        </div>
        <div>
          <h3 class="tw-text-xl tw-font-semibold">{{ blog.author.username || blog.author.name }}</h3>
          <p v-if="blog.author.bio" class="tw-text-gray-600 tw-mt-2">{{ blog.author.bio }}</p>
          <div v-if="blog.author.social_links" class="tw-flex tw-gap-3 tw-mt-3">
            <a v-for="(link, platform) in blog.author.social_links" :key="platform" :href="link" target="_blank"
              class="tw-text-gray-500 hover:tw-text-primary">
              <i :class="getSocialIcon(platform)"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useBlog } from '~/composables/useBlog'
import { useAuth } from '~/composables/useAuth'

const { isAuthenticated } = useAuth()
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
    // Không fetch relatedBlogs, comments, view count
  } catch (err) {
    error.value = 'Blog not found'
    blog.value = null
  }
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

<style scoped>
.tw-prose :deep(img) {
  border-radius: 0.5rem;
  margin: 1rem 0;
}

.tw-prose :deep(a) {
  color: #3bb77e;
  text-decoration: underline;
}

.tw-prose :deep(ul) {
  list-style-type: disc;
  padding-left: 1.5rem;
  margin-bottom: 1.25rem;
}

.tw-prose :deep(h2) {
  font-size: 1.5rem;
  font-weight: 600;
  margin-top: 2rem;
  margin-bottom: 1rem;
}

.tw-prose :deep(h3) {
  font-size: 1.25rem;
  font-weight: 600;
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
}

.tw-prose :deep(blockquote) {
  border-left: 4px solid #3bb77e;
  padding-left: 1rem;
  color: #555;
  font-style: italic;
  margin: 1rem 0;
}

.tw-prose :deep(pre) {
  background-color: #f8f8f8;
  padding: 1rem;
  border-radius: 0.5rem;
  overflow-x: auto;
}

.tw-prose :deep(code) {
  background-color: #f8f8f8;
  padding: 0.2rem 0.4rem;
  border-radius: 0.25rem;
  font-family: monospace;
}
</style>