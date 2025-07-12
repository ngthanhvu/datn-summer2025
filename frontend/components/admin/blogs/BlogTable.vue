<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">

        <!-- Error state -->
        <div v-if="error"
            class="tw-bg-red-100 tw-border tw-border-red-400 tw-text-red-700 tw-px-4 tw-py-3 tw-rounded tw-mb-4">
            {{ error }}
        </div>

        <!-- Filters section -->
        <!-- <div class="tw-flex tw-gap-4 tw-mb-4">
            <select v-model="selectedStatus" class="tw-border tw-rounded tw-px-2 tw-py-1 tw-w-40">
                <option value="">Tất cả trạng thái</option>
                <option value="draft">Bản nháp</option>
                <option value="published">Đã xuất bản</option>
                <option value="archived">Lưu trữ</option>
            </select>

            <input v-model="searchQuery" type="text" class="tw-border tw-rounded tw-px-2 tw-py-1 tw-w-40"
                placeholder="Tìm kiếm bài viết..." />
        </div> -->

        <!-- Table section -->
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left tw-bg-white tw-text-sm">
                <thead>
                    <tr class="tw-border-b tw-bg-gray-50">
                        <th class="tw-px-4 tw-py-3">Hình ảnh</th>
                        <th class="tw-px-4 tw-py-3">Tên bài viết</th>
                        <th class="tw-px-4 tw-py-3">Mô tả</th>
                        <th class="tw-px-4 tw-py-3">Tác giả</th>
                        <th class="tw-px-4 tw-py-3">Trạng thái</th>
                        <th class="tw-px-4 tw-py-3">Ngày tạo</th>
                        <th class="tw-px-4 tw-py-3">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="loading">
                        <tr v-for="n in 5" :key="n">
                            <td class="tw-px-2 tw-py-1">
                                <div class="tw-bg-gray-200 tw-rounded tw-w-10 tw-h-10 tw-animate-pulse"></div>
                            </td>
                            <td class="tw-px-2 tw-py-1">
                                <div class="tw-bg-gray-200 tw-h-4 tw-rounded tw-w-1/2 tw-mb-2 tw-animate-pulse"></div>
                            </td>
                            <td class="tw-px-2 tw-py-1">
                                <div class="tw-bg-gray-200 tw-h-3 tw-rounded tw-w-1/3 tw-animate-pulse"></div>
                            </td>
                            <td class="tw-px-2 tw-py-1">
                                <div class="tw-bg-gray-200 tw-h-4 tw-rounded tw-w-24 tw-animate-pulse"></div>
                            </td>
                            <td class="tw-px-2 tw-py-1">
                                <div class="tw-bg-gray-200 tw-h-4 tw-rounded tw-w-16 tw-animate-pulse"></div>
                            </td>
                            <td class="tw-px-2 tw-py-1">
                                <div class="tw-bg-gray-200 tw-h-4 tw-rounded tw-w-20 tw-animate-pulse"></div>
                            </td>
                            <td class="tw-px-2 tw-py-1">
                                <div class="tw-bg-gray-200 tw-h-4 tw-rounded tw-w-16 tw-animate-pulse"></div>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr v-for="blog in blogs" :key="blog.id" class="tw-border-b hover:tw-bg-gray-50">
                            <td class="tw-px-2 tw-py-1">
                                <img v-if="blog.image" :src="getImageUrl(blog.image)"
                                    class="tw-w-10 tw-h-10 tw-object-cover tw-rounded" :alt="blog.title" />
                                <div v-else
                                    class="tw-w-10 tw-h-10 tw-bg-gray-200 tw-rounded tw-flex tw-items-center tw-justify-center">
                                    <span class="tw-text-gray-400">No Image</span>
                                </div>
                            </td>
                            <td class="tw-px-2 tw-py-1 tw-font-medium">
                                {{ blog.title }}
                            </td>
                            <td class="tw-px-2 tw-py-1 tw-max-w-xs tw-truncate">
                                {{ blog.description }}
                            </td>
                            <td class="tw-px-2 tw-py-1">
                                {{ blog.author?.username || 'Unknown' }}
                            </td>
                            <td class="tw-px-2 tw-py-1">
                                <span :class="getStatusBadgeClass(blog.status)">
                                    {{ getStatusLabel(blog.status) }}
                                </span>
                            </td>
                            <td class="tw-px-2 tw-py-1">
                                {{ formatDate(blog.created_at) }}
                            </td>
                            <td class="tw-px-2 tw-py-1">
                                <div class="tw-flex tw-items-center tw-gap-2">
                                    <button @click="handleEdit(blog)"
                                        class="tw-inline-flex tw-items-center tw-p-1.5 tw-text-blue-600 hover:tw-text-blue-900 hover:tw-bg-blue-50 tw-rounded-lg tw-transition-colors tw-duration-150"
                                        title="Chỉnh sửa bài viết">
                                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button @click="handleDelete(blog)"
                                        class="tw-inline-flex tw-items-center tw-p-1.5 tw-text-red-600 hover:tw-text-red-900 hover:tw-bg-red-50 tw-rounded-lg tw-transition-colors tw-duration-150"
                                        title="Xóa bài viết">
                                        <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="blogs.length === 0">
                            <td colspan="8" class="tw-px-3 tw-py-2 tw-text-center tw-text-gray-500">
                                Không có dữ liệu
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination" class="tw-flex tw-justify-between tw-items-center tw-mt-4">
            <div class="tw-text-sm tw-text-gray-500">
                Hiển thị {{ blogs.length }} trong tổng số {{ pagination.total }} bài viết
            </div>
            <div class="tw-flex tw-gap-2">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                    class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm hover:tw-bg-gray-50 disabled:tw-opacity-50">
                    Trước
                </button>
                <button v-for="page in pagination.last_page" :key="page" @click="changePage(page)"
                    :class="{ 'tw-bg-blue-500 tw-text-white': pagination.current_page === page }"
                    class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm hover:tw-bg-gray-50">
                    {{ page }}
                </button>
                <button @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm hover:tw-bg-gray-50 disabled:tw-opacity-50">
                    Sau
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const props = defineProps({
    blogs: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    error: { type: [String, null], default: null },
    pagination: { type: Object, default: null }
})
const emit = defineEmits(['delete', 'refresh'])
const router = useRouter()

const handleEdit = (blog) => {
    router.push(`/admin/blogs/${blog.id}/edit`)
}

const handleDelete = async (blog) => {
    if (confirm(`Bạn có chắc chắn muốn xóa bài viết "${blog.title}"?`)) {
        emit('delete', blog.id)
        emit('refresh')
    }
}

const changePage = (page) => {
    emit('refresh', page)
}

function getStatusLabel(status) {
    const labels = {
        draft: 'Bản nháp',
        published: 'Đã xuất bản',
        archived: 'Lưu trữ'
    }
    return labels[status] || status
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'published':
            return 'tw-bg-green-100 tw-text-green-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'archived':
            return 'tw-bg-orange-100 tw-text-orange-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'draft':
            return 'tw-bg-blue-100 tw-text-blue-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        default:
            return 'tw-bg-gray-100 tw-text-gray-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
    }
}

function formatDate(dateString) {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('vi-VN')
}

function getImageUrl(path) {
    if (!path) return ''
    return path.startsWith('/storage/') ? `http://localhost:8000${path}` : path
}
</script>