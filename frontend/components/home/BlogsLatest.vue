<template>
    <h2 class="tw-text-2xl tw-font-bold tw-text-gray-800 tw-mb-3">Bài viết nổi bật</h2>
    <div>
        <swiper :modules="[SwiperPagination]" :slides-per-view="1" :space-between="0" :pagination="{ clickable: true }"
            :breakpoints="{
                '640': { slidesPerView: 2, spaceBetween: 16 },
                '1024': { slidesPerView: 3, spaceBetween: 24 }
            }" class="tw-w-full">
            <swiper-slide v-for="blog in latestBlogs" :key="blog.id">
                <NuxtLink :to="`/blog/${blog.slug}`" class="tw-no-underline tw-text-gray-900">
                    <div class="blog-card">
                        <div class="image-container">
                            <img v-if="blog.image" :src="blog.image" :alt="blog.title"
                                class="tw-w-full tw-h-full tw-object-cover" />
                            <div v-else
                                class="tw-bg-gray-200 tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-gray-500">Không có hình ảnh</span>
                            </div>
                        </div>
                        <div class="blog-card-content">
                            <div class="tw-flex tw-items-center tw-text-sm tw-text-gray-500 tw-mb-2">
                                <span>{{ formatDate(blog.published_at || blog.created_at) }}</span>
                                <span class="tw-mx-2">•</span>
                                <span>{{ blog.author?.username || 'Unknown' }}</span>
                            </div>
                            <h3 class="tw-text-xl tw-font-semibold tw-mb-2 line-clamp-2">{{ blog.title }}</h3>
                            <p class="tw-text-gray-600 tw-mb-4 line-clamp-3">{{ blog.description }}</p>
                            <div class="tw-text-[#81aacc] tw-font-medium hover:tw-underline">Đọc thêm</div>
                        </div>
                    </div>
                </NuxtLink>
            </swiper-slide>
        </swiper>
    </div>
</template>

<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Pagination as SwiperPagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import { computed, onMounted } from 'vue'
import { useBlog } from '~/composables/useBlog'

const { blogs, fetchBlogs } = useBlog()
onMounted(fetchBlogs)

const latestBlogs = computed(() =>
    blogs.value
        ? blogs.value
            .filter(blog => blog.status === 'published')
            .sort((a, b) => new Date(b.published_at || b.created_at) - new Date(a.published_at || a.created_at))
            .slice(0, 3)
        : []
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
.swiper-slide {
    height: auto !important;
    display: flex;
    align-items: stretch;
}

.blog-card {
    display: flex;
    flex-direction: column;
    border: 1.5px solid transparent;
    border-radius: 0.5rem;
    background: #fff;
    overflow: hidden;
    transition: border 0.2s, box-shadow 0.2s;
}

/* Ảnh cố định chiều cao */
.image-container {
    height: 180px;
    min-height: 180px;
    max-height: 180px;
    overflow: hidden;
    position: relative;
}

/* Phần nội dung không làm vỡ layout */
.blog-card-content {
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    overflow: hidden;
    padding: 1.5rem;
    min-height: 0;
}

/* Giới hạn title 2 dòng */
h3.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.5em;
}

/* Giới hạn mô tả 3 dòng */
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.5em;
}

/* Hover đổi màu title */
.blog-card:hover h3.line-clamp-2 {
    color: #81AACC;
}

.tw-text-primary {
    color: #3bb77e;
}

/* Tùy chỉnh dấu chấm swiper */
:deep(.swiper-pagination-bullet-active) {
    background: #000;
}
</style>
