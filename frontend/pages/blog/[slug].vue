<template>
  <div class="tw-max-w-6xl tw-mx-auto tw-px-4 tw-py-8">
    <div v-if="loading" class="tw-text-center tw-py-12">
      <div class="tw-inline-block tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-b-2 tw-border-blue-500"></div>
    </div>
    <div v-else-if="error"
      class="tw-bg-red-100 tw-border tw-border-red-400 tw-text-red-700 tw-px-4 tw-py-3 tw-rounded tw-mb-6">
      {{ error }}
    </div>
    <div v-else-if="blog">
      <div class="tw-flex tw-flex-col lg:tw-flex-row tw-gap-8">
        <!-- Main Content -->
        <div class="tw-flex-1 tw-min-w-0 tw-bg-white tw-p-8">
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
          <div v-if="blog.image" class="tw-flex tw-justify-center tw-my-6">
            <img :src="blog.image" :alt="blog.title" class="blog-image" />
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
            <div class="editor-content" v-html="blog.content"></div>
          </article>
          <div class="tw-flex tw-items-center tw-gap-4 tw-pt-4 tw-border-t">
            <span class="tw-text-gray-600">Chia sẻ:</span>
            <a v-for="social in socialPlatforms" :key="social.name" :href="getShareUrl(social)" target="_blank"
              class="tw-text-gray-500 hover:tw-text-primary tw-text-xl" :title="'Share on ' + social.name">
              <i :class="social.icon"></i>
            </a>
          </div>
          <div v-if="blog.author"
            class="tw-bg-gray-50 tw-rounded-xl tw-p-6 tw-flex tw-flex-col md:tw-flex-row tw-gap-2 tw-mt-8">
            <div class="tw-flex-shrink-0">
              <img
                :src="blog.author.avatar || '/https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'"
                :alt="blog.author.username || blog.author.name"
                class="tw-w-10 tw-h-10 tw-rounded-full tw-object-cover" />
            </div>
            <div>
              <h3 class="tw-text-sm tw-font-semibold">{{ blog.author.username || blog.author.name }}</h3>
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
        <!-- Sidebar -->
        <aside class="tw-w-full lg:tw-w-80 tw-flex-shrink-0">
          <div class="tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm tw-mb-6">
            <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Bài viết liên quan</h2>
            <div class="tw-text-gray-500 tw-text-sm">(Đang cập nhật...)</div>
          </div>
          <div class="tw-bg-white tw-rounded-xl tw-p-6 tw-shadow-sm">
            <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Danh mục</h2>
            <div class="tw-text-gray-500 tw-text-sm">(Đang cập nhật...)</div>
          </div>
        </aside>
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
  } catch (err) {
    error.value = 'Blog not found'
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

<style scoped>
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
</style>
<style src="@/assets/css/ckeditor-content.css"></style>
