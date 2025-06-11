<template>
    <div class="tw-p-4">
        <!-- Header -->
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <h2 class="tw-text-2xl tw-font-semibold tw-text-gray-800">Danh sách bài viết</h2>
            <button
                @click="$emit('create')"
                class="tw-bg-primary tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-primary-dark tw-transition-colors"
            >
                Thêm bài viết mới
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading && blogs.length === 0" class="tw-text-center tw-py-8">
            <div class="tw-inline-block tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-b-2 tw-border-primary"></div>
            <p class="tw-mt-2 tw-text-gray-600">Đang tải...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="tw-bg-red-100 tw-border tw-border-red-400 tw-text-red-700 tw-px-4 tw-py-3 tw-rounded tw-mb-4">
            {{ error }}
            <button @click="handleRefresh" class="tw-ml-2 tw-underline">Thử lại</button>
        </div>

        <!-- Empty State -->
        <div v-else-if="!loading && blogs.length === 0" class="tw-text-center tw-py-12">
            <div class="tw-text-gray-400 tw-text-6xl tw-mb-4">📝</div>
            <h3 class="tw-text-xl tw-font-medium tw-text-gray-600 tw-mb-2">Chưa có bài viết nào</h3>
            <p class="tw-text-gray-500 tw-mb-4">Tạo bài viết đầu tiên của bạn ngay bây giờ!</p>
            <button
                @click="$emit('create')"
                class="tw-bg-primary tw-text-white tw-px-6 tw-py-2 tw-rounded hover:tw-bg-primary-dark tw-transition-colors"
            >
                Tạo bài viết mới
            </button>
        </div>

        <!-- Blogs Grid -->
        <div v-else class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
            <div
                v-for="blog in blogs"
                :key="blog.id"
                class="tw-bg-white tw-border tw-border-gray-200 tw-rounded-lg tw-shadow-sm hover:tw-shadow-md tw-transition-shadow tw-overflow-hidden"
            >
                <!-- Blog Header -->
                <div class="tw-p-4 tw-border-b tw-border-gray-100">
                    <div class="tw-flex tw-justify-between tw-items-start tw-mb-2">
                        <span
                            :class="{
                                'tw-bg-green-100 tw-text-green-800': blog.status === 'published',
                                'tw-bg-yellow-100 tw-text-yellow-800': blog.status === 'draft',
                                'tw-bg-gray-100 tw-text-gray-800': blog.status === 'archived'
                            }"
                            class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-full"
                        >
                            {{ getStatusText(blog.status) }}
                        </span>
                        
                        <!-- Actions Dropdown -->
                        <div class="tw-relative">
                            <button
                                @click="toggleDropdown(blog.id)"
                                class="tw-text-gray-400 hover:tw-text-gray-600 tw-p-1 tw-rounded"
                            >
                                <svg class="tw-w-4 tw-h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                </svg>
                            </button>
                            
                            <div
                                v-if="activeDropdown === blog.id"
                                @click.stop
                                class="tw-absolute tw-right-0 tw-mt-1 tw-w-32 tw-bg-white tw-border tw-border-gray-200 tw-rounded-md tw-shadow-lg tw-z-10"
                            >
                                <button
                                    @click="handleView(blog)"
                                    class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 hover:tw-bg-gray-100"
                                >
                                    Xem
                                </button>
                                <button
                                    @click="handleEdit(blog)"
                                    class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-gray-700 hover:tw-bg-gray-100"
                                >
                                    Sửa
                                </button>
                                <button
                                    @click="handleDelete(blog)"
                                    class="tw-block tw-w-full tw-text-left tw-px-4 tw-py-2 tw-text-sm tw-text-red-600 hover:tw-bg-red-50"
                                >
                                    Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <h3 class="tw-font-semibold tw-text-gray-900 tw-mb-2 tw-line-clamp-2">
                        {{ blog.title }}
                    </h3>
                    
                    <p class="tw-text-gray-600 tw-text-sm tw-line-clamp-3 tw-mb-3">
                        {{ blog.description }}
                    </p>
                </div>

                <!-- Blog Content Preview -->
                <div class="tw-p-4">
                    <div
                        class="tw-text-gray-700 tw-text-sm tw-line-clamp-4 tw-mb-4"
                        v-html="getContentPreview(blog.content)"
                    ></div>
                </div>

                <!-- Blog Footer -->
                <div class="tw-px-4 tw-pb-4 tw-text-xs tw-text-gray-500">
                    <div class="tw-flex tw-justify-between tw-items-center">
                        <span v-if="blog.author">
                            Tác giả: {{ blog.author.name }}
                        </span>
                        <span v-if="blog.published_at">
                            {{ formatDate(blog.published_at) }}
                        </span>
                        <span v-else>
                            {{ formatDate(blog.created_at) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="tw-flex tw-justify-center tw-items-center tw-mt-8 tw-space-x-2">
            <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                class="tw-px-3 tw-py-2 tw-text-sm tw-border tw-border-gray-300 tw-rounded tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed"
            >
                Trước
            </button>
            
            <template v-for="page in getVisiblePages()" :key="page">
                <button
                    v-if="page !== '...'"
                    @click="changePage(page)"
                    :class="{
                        'tw-bg-primary tw-text-white': page === pagination.current_page,
                        'tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50': page !== pagination.current_page
                    }"
                    class="tw-px-3 tw-py-2 tw-text-sm tw-border tw-border-gray-300 tw-rounded"
                >
                    {{ page }}
                </button>
                <span v-else class="tw-px-3 tw-py-2 tw-text-sm tw-text-gray-500">...</span>
            </template>
            
            <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                class="tw-px-3 tw-py-2 tw-text-sm tw-border tw-border-gray-300 tw-rounded tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50 disabled:tw-opacity-50 disabled:tw-cursor-not-allowed"
            >
                Sau
            </button>
        </div>

        <!-- Pagination Info -->
        <div v-if="pagination" class="tw-text-center tw-text-sm tw-text-gray-500 tw-mt-4">
            Hiển thị {{ pagination.from }} - {{ pagination.to }} trong tổng số {{ pagination.total }} bài viết
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50"
            @click="closeDeleteModal"
        >
            <div
                @click.stop
                class="tw-bg-white tw-rounded-lg tw-p-6 tw-max-w-md tw-mx-4"
            >
                <h3 class="tw-text-lg tw-font-semibold tw-text-gray-900 tw-mb-4">Xác nhận xóa</h3>
                <p class="tw-text-gray-600 tw-mb-6">
                    Bạn có chắc chắn muốn xóa bài viết "{{ blogToDelete?.title }}"? Hành động này không thể hoàn tác.
                </p>
                <div class="tw-flex tw-justify-end tw-space-x-3">
                    <button
                        @click="closeDeleteModal"
                        class="tw-px-4 tw-py-2 tw-text-gray-600 tw-border tw-border-gray-300 tw-rounded hover:tw-bg-gray-50"
                    >
                        Hủy
                    </button>
                    <button
                        @click="confirmDelete"
                        :disabled="deleteLoading"
                        class="tw-px-4 tw-py-2 tw-bg-red-600 tw-text-white tw-rounded hover:tw-bg-red-700 disabled:tw-opacity-50"
                    >
                        <span v-if="deleteLoading">Đang xóa...</span>
                        <span v-else>Xóa</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useBlog } from '@/composables/useBlog'

const emit = defineEmits(['create', 'edit', 'view'])

// Composable
const { blogs, loading, error, pagination, fetchBlogs, deleteBlog, clearError } = useBlog()

// Local state
const activeDropdown = ref(null)
const showDeleteModal = ref(false)
const blogToDelete = ref(null)
const deleteLoading = ref(false)

// Methods
const handleRefresh = async () => {
    clearError()
    await fetchBlogs()
}

const changePage = async (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        await fetchBlogs(page)
    }
}

const getVisiblePages = () => {
    if (!pagination.value) return []
    
    const current = pagination.value.current_page
    const last = pagination.value.last_page
    const pages = []
    
    if (last <= 7) {
        for (let i = 1; i <= last; i++) {
            pages.push(i)
        }
    } else {
        if (current <= 4) {
            for (let i = 1; i <= 5; i++) pages.push(i)
            pages.push('...')
            pages.push(last)
        } else if (current >= last - 3) {
            pages.push(1)
            pages.push('...')
            for (let i = last - 4; i <= last; i++) pages.push(i)
        } else {
            pages.push(1)
            pages.push('...')
            for (let i = current - 1; i <= current + 1; i++) pages.push(i)
            pages.push('...')
            pages.push(last)
        }
    }
    
    return pages
}

const toggleDropdown = (blogId) => {
    activeDropdown.value = activeDropdown.value === blogId ? null : blogId
}

const closeDropdown = () => {
    activeDropdown.value = null
}

const handleView = (blog) => {
    closeDropdown()
    emit('view', blog)
}

const handleEdit = (blog) => {
    closeDropdown()
    emit('edit', blog)
}

const handleDelete = (blog) => {
    closeDropdown()
    blogToDelete.value = blog
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    blogToDelete.value = null
}

const confirmDelete = async () => {
    if (!blogToDelete.value) return
    
    deleteLoading.value = true
    try {
        await deleteBlog(blogToDelete.value.id)
        closeDeleteModal()
    } catch (err) {
        console.error('Error deleting blog:', err)
    } finally {
        deleteLoading.value = false
    }
}

const getStatusText = (status) => {
    const statusMap = {
        'published': 'Đã xuất bản',
        'draft': 'Bản nháp',
        'archived': 'Lưu trữ'
    }
    return statusMap[status] || status
}

const getContentPreview = (content) => {
    if (!content) return ''
    
    // Remove HTML tags and get first 150 characters
    const textContent = content.replace(/<[^>]*>/g, '')
    return textContent.length > 150 ? textContent.substring(0, 150) + '...' : textContent
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    
    const date = new Date(dateString)
    return date.toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

// Click outside to close dropdown
const handleClickOutside = (event) => {
    if (!event.target.closest('.tw-relative')) {
        closeDropdown()
    }
}

// Lifecycle
onMounted(async () => {
    await fetchBlogs()
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}

.tw-line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.tw-line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.tw-line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>