<template>
  <div class="min-h-[80vh] bg-gradient-to-b from-blue-50/60 to-white py-12 px-2">
    <div v-if="loading" class="flex justify-center items-center py-16">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="page">
      <!-- Header: Icon + Title -->
      <div class="flex flex-col items-center mb-8">
        <div v-if="page.type === 'policy'" class="mb-3">
          <i class="fas fa-shield-alt text-blue-500 text-5xl drop-shadow"></i>
        </div>
        <div v-else-if="page.type === 'support'" class="mb-3">
          <i class="fas fa-headset text-green-500 text-5xl drop-shadow"></i>
        </div>
        <div v-else-if="page.type === 'about'" class="mb-3">
          <i class="fas fa-info-circle text-purple-500 text-5xl drop-shadow"></i>
        </div>
        <div v-else class="mb-3">
          <i class="fas fa-file-alt text-gray-400 text-5xl drop-shadow"></i>
        </div>
        <h1
          class="text-4xl md:text-5xl font-extrabold text-gray-900 text-center mb-3 tracking-tight bg-gradient-to-r from-blue-700 via-green-600 to-blue-400 bg-clip-text">
          {{ page.title }}
        </h1>
        <!-- Meta info -->
        <div class="flex flex-wrap justify-center items-center gap-3 text-sm mb-2">
          <span
            class="inline-flex items-center px-3 py-1 rounded-full bg-white/80 border border-gray-200 shadow-sm text-gray-700 text-xs font-medium">
            <i class="fas fa-calendar-alt mr-1 text-blue-400"></i> {{ formatDate(page.updated_at) }}
          </span>
          <span
            :class="['inline-flex items-center px-3 py-1 rounded-full border shadow-sm text-xs font-medium', typeBadgeClass(page.type)]">
            <i class="fas fa-tag mr-1"></i> {{ typeLabel(page.type) }}
          </span>
          <span
            :class="['inline-flex items-center px-3 py-1 rounded-full border shadow-sm text-xs font-medium', page.status ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700']">
            <i :class="page.status ? 'fas fa-check-circle mr-1' : 'fas fa-times-circle mr-1'"></i>
            {{ page.status ? 'Hoạt động' : 'Không hoạt động' }}
          </span>
        </div>
      </div>

      <!-- Content: Không card, chỉ căn giữa, max-w-3xl, font lớn, line-height thoáng -->
      <div class="mx-auto max-w-3xl px-2 md:px-0">
        <div class="prose prose-lg max-w-none text-gray-800" v-html="formattedContent"></div>
      </div>
    </div>

    <div v-else class="flex flex-col items-center justify-center py-16">
      <div class="bg-white rounded-full shadow-lg p-6 mb-4">
        <svg class="h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
          </path>
        </svg>
      </div>
      <h3 class="text-xl font-semibold text-gray-800 mb-2">Không tìm thấy trang</h3>
      <p class="text-gray-500 mb-4">Trang bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>
      <a href="/"
        class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">Về trang
        chủ</a>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { usePages } from '../../composable/usePages'

const route = useRoute()
const { fetchPageBySlug, loading, page } = usePages()

const formattedContent = computed(() => {
  if (!page.value?.content) return ''
  let content = page.value.content
  // Bold text: **text** -> <strong>text</strong>
  content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
  // Italic text: *text* -> <em>text</em>
  content = content.replace(/\*(.*?)\*/g, '<em>$1</em>')
  // Underlined text: __text__ -> <u>text</u>
  content = content.replace(/__(.*?)__/g, '<u>$1</u>')
  // Links: [text](url) -> <a href="url">text</a>
  content = content.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-blue-600 hover:text-blue-800 underline" target="_blank">$1</a>')
  // Unordered lists: - item -> <li>item</li>
  content = content.replace(/^- (.+)$/gm, '<li>$1</li>')
  content = content.replace(/(<li>.*<\/li>)/s, '<ul class="list-disc list-inside mb-4">$1</ul>')
  // Ordered lists: 1. item -> <li>item</li>
  content = content.replace(/^\d+\. (.+)$/gm, '<li>$1</li>')
  content = content.replace(/(<li>.*<\/li>)/s, '<ol class="list-decimal list-inside mb-4">$1</ol>')
  // Paragraphs
  content = content.replace(/\n\n/g, '</p><p class="mb-4">')
  content = `<p class="mb-4">${content}</p>`
  return content
})

const typeLabel = (type) => {
  const map = { policy: 'Chính sách', support: 'Hỗ trợ', about: 'Giới thiệu', other: 'Khác' }
  return map[type] || type
}
const typeBadgeClass = (type) => {
  return {
    policy: 'bg-blue-100 text-blue-700',
    support: 'bg-green-100 text-green-700',
    about: 'bg-purple-100 text-purple-700',
    other: 'bg-gray-100 text-gray-700',
  }[type] || 'bg-gray-100 text-gray-700'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const loadPage = async () => {
  try {
    const slug = route.params.slug
    if (slug) {
      await fetchPageBySlug(slug)
    }
  } catch (error) {
    console.error('Error loading page:', error)
  }
}

onMounted(() => {
  loadPage()
})
</script>

<style scoped>
.prose {
  line-height: 1.7;
  font-size: 1.1rem;
}

.prose strong {
  font-weight: 700;
}

.prose em {
  font-style: italic;
}

.prose u {
  text-decoration: underline;
}

.prose ul,
.prose ol {
  margin-bottom: 1em;
  padding-left: 1.5em;
}

.prose li {
  margin-bottom: 0.5em;
}

.prose a {
  color: #2563eb;
  text-decoration: underline;
}

.prose a:hover {
  color: #1d4ed8;
}

.prose p {
  margin-bottom: 1em;
}

@media (max-width: 640px) {
  .prose {
    font-size: 1rem;
    padding: 0;
  }

  .container {
    padding: 0 0.5rem;
  }
}
</style>