<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <!-- Loading state -->
        <div v-if="loading" class="tw-flex tw-justify-center tw-py-8">
            <div class="tw-animate-spin tw-rounded-full tw-h-12 tw-w-12 tw-border-t-2 tw-border-b-2 tw-border-primary">
            </div>
        </div>

        <!-- Error state -->
        <div v-if="error"
            class="tw-bg-red-100 tw-border tw-border-red-400 tw-text-red-700 tw-px-4 tw-py-3 tw-rounded tw-mb-4">
            {{ error }}
        </div>

        <!-- Filters section -->
        <div class="tw-flex tw-gap-4 tw-mb-4">
            <select v-model="selectedStatus" class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56">
                <option value="">Tất cả trạng thái</option>
                <option value="draft">Bản nháp</option>
                <option value="published">Đã xuất bản</option>
                <option value="archived">Lưu trữ</option>
            </select>

            <input v-model="searchQuery" type="text" class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56"
                placeholder="Tìm kiếm bài viết..." />
        </div>

        <!-- Table section -->
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left tw-bg-white">
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
                    <tr v-for="blog in blogs" :key="blog.id" class="tw-border-b hover:tw-bg-gray-50">
                        <td class="tw-px-4 tw-py-3">
                            <img v-if="blog.image" :src="getImageUrl(blog.image)"
                                class="tw-w-14 tw-h-14 tw-object-cover tw-rounded" :alt="blog.title" />
                            <div v-else
                                class="tw-w-14 tw-h-14 tw-bg-gray-200 tw-rounded tw-flex tw-items-center tw-justify-center">
                                <span class="tw-text-gray-400">No Image</span>
                            </div>
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-font-medium">
                            {{ blog.title }}
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-max-w-xs tw-truncate">
                            {{ blog.description }}
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            {{ blog.author?.username || 'Unknown' }}
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <span :class="getStatusBadgeClass(blog.status)">
                                {{ getStatusLabel(blog.status) }}
                            </span>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            {{ formatDate(blog.created_at) }}
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-flex tw-gap-2">
                            <button @click="handleEdit(blog)"
                                class="tw-bg-teal-600 tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-teal-700">
                                Sửa
                            </button>
                            <button @click="handleDelete(blog)"
                                class="tw-border tw-border-gray-300 tw-rounded tw-px-4 tw-py-2 tw-bg-white hover:tw-bg-gray-100">
                                Xóa
                            </button>
                        </td>
                    </tr>
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
import { ref, onMounted, watch } from 'vue'
import { useBlog } from '@/composables/useBlog'
import { useRouter } from 'vue-router'

const { blogs, loading, error, pagination, fetchBlogs, deleteBlog } = useBlog()
const router = useRouter()

const selectedStatus = ref('')
const searchQuery = ref('')

onMounted(async () => {
    await fetchBlogs()
})

watch([selectedStatus, searchQuery], async () => {
    await fetchBlogs(1, {
        status: selectedStatus.value,
        search: searchQuery.value
    })
})

const changePage = async (page) => {
    if (page < 1 || page > pagination.value.last_page) return
    await fetchBlogs(page, {
        status: selectedStatus.value,
        search: searchQuery.value
    })
}

const handleEdit = (blog) => {
    router.push(`/admin/blogs/${blog.id}/edit`)
}

const handleDelete = async (blog) => {
    if (confirm(`Bạn có chắc chắn muốn xóa bài viết "${blog.title}"?`)) {
        await deleteBlog(blog.id)
        await fetchBlogs(pagination.value.current_page)
    }
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