<template>
  <div class="tw-container tw-mx-auto tw-px-4 tw-py-8">
    <div class="tw-text-center tw-mb-12">
      <h1 class="tw-text-4xl tw-font-bold tw-mb-4">Tin tức mới nhất</h1>
      <p class="tw-text-lg tw-text-gray-600">Cập nhật những bài viết và kiến thức mới nhất</p>
    </div>

    <div v-if="loading" class="tw-text-center tw-py-12">
      <div class="tw-inline-block tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-b-2 tw-border-blue-500"></div>
    </div>

    <!-- <div v-else-if="error" class="tw-bg-red-100 tw-border tw-border-red-400 tw-text-red-700 tw-px-4 tw-py-3 tw-rounded tw-mb-6">
      {{ error }}
    </div> -->

    <div v-else>
      <div v-if="filteredBlogs.length > 0"
        class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-8 tw-mb-8">
        <div v-for="blog in filteredBlogs" :key="blog.id"
          class="tw-bg-white tw-rounded-lg tw-shadow-md tw-overflow-hidden hover:tw-shadow-lg tw-transition-shadow">
          <NuxtLink :to="`/blog/${blog.slug}`" class="tw-no-underline tw-text-gray-900">
            <div class="tw-relative tw-h-48 tw-overflow-hidden">
              <img v-if="blog.image" :src="blog.image" :alt="blog.title" class="tw-w-full tw-h-full tw-object-cover" />
              <div v-else class="tw-bg-gray-200 tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center">
                <span class="tw-text-gray-500">Không có hình ảnh</span>
              </div>
            </div>
            <div class="tw-p-6">
              <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-500 tw-mb-2">
                <span>{{ formatDate(blog.published_at || blog.created_at) }}</span>
                <span class="tw-mx-2">•</span>
                <span>{{ blog.author?.username || 'Unknown' }}</span>
              </div>
              <h3 class="tw-text-xl tw-font-semibold tw-mb-2 tw-line-clamp-2">{{ blog.title }}</h3>
              <p class="tw-text-gray-600 tw-mb-4 tw-line-clamp-3">{{ blog.description }}</p>
              <div class="tw-text-primary tw-font-medium hover:tw-underline">Đọc thêm</div>
            </div>
          </NuxtLink>
        </div>
      </div>
      <div v-else class="tw-text-center tw-text-gray-500 tw-py-12">
        Không có bài viết nào.
      </div>
      <div v-if="pagination && filteredBlogs.length > 0" class="tw-flex tw-justify-center tw-mt-8">
        <button v-for="page in pagination.last_page" :key="page" @click="fetchBlogs(page)" :class="{
          'tw-bg-[#81aacc] tw-text-white': page === pagination.current_page,
          'tw-bg-white tw-text-gray-700': page !== pagination.current_page
        }" class="tw-px-4 tw-py-2 tw-mx-1 tw-rounded tw-border tw-border-gray-300 hover:tw-bg-[#4a8abe]">
          {{ page }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useBlog } from '~/composables/useBlog'

const { blogs, loading, error, pagination, fetchBlogs } = useBlog()
onMounted(fetchBlogs)

const filteredBlogs = computed(() =>
  blogs.value ? blogs.value.filter(blog => blog.status === 'published') : []
)

const formatDate = dateString => {
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

.tw-text-primary {
  color: #81aacc;
}
</style>