<template>
    <div class="bg-white rounded-lg shadow p-6">

        <!-- Error state -->
        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ error }}
        </div>

        <!-- Filters section -->
        <!-- <div class="flex gap-4 mb-4">
            <select v-model="selectedStatus" class="border rounded px-2 py-1 w-40">
                <option value="">Tất cả trạng thái</option>
                <option value="draft">Bản nháp</option>
                <option value="published">Đã xuất bản</option>
                <option value="archived">Lưu trữ</option>
            </select>

            <input v-model="searchQuery" type="text" class="border rounded px-2 py-1 w-40"
                placeholder="Tìm kiếm bài viết..." />
        </div> -->

        <!-- Table section -->
        <div class="overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <table class="w-full text-left bg-white text-sm">
                <thead>
                    <tr class="border-b border-gray-300">
                        <th class="px-4 py-3 text-center">#</th>
                        <th class="px-4 py-3 text-center">Hình ảnh</th>
                        <th class="px-4 py-3 text-center">Tên bài viết</th>
                        <th class="px-4 py-3 text-center">Mô tả</th>
                        <th class="px-4 py-3 text-center">Tác giả</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3 text-center">Ngày tạo</th>
                        <th class="px-4 py-3 text-left">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="loading">
                        <tr v-for="n in 6" :key="n">
                            <td class="px-2 py-1">
                                <div class="bg-gray-200 rounded w-10 h-10 animate-pulse"></div>
                            </td>
                            <td class="px-2 py-1">
                                <div class="bg-gray-200 h-4 rounded w-1/2 mb-2 animate-pulse"></div>
                            </td>
                            <td class="px-2 py-1">
                                <div class="bg-gray-200 h-3 rounded w-1/3 animate-pulse"></div>
                            </td>
                            <td class="px-2 py-1">
                                <div class="bg-gray-200 h-4 rounded w-24 animate-pulse"></div>
                            </td>
                            <td class="px-2 py-1">
                                <div class="bg-gray-200 h-4 rounded w-16 animate-pulse"></div>
                            </td>
                            <td class="px-2 py-1">
                                <div class="bg-gray-200 h-4 rounded w-20 animate-pulse"></div>
                            </td>
                            <td class="px-2 py-1">
                                <div class="bg-gray-200 h-4 rounded w-16 animate-pulse"></div>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr v-for="blog in blogs" :key="blog.id" class="border-b border-gray-300 hover:bg-gray-50">
                            <td class="px-2 py-1 text-center">
                                {{ blog.id }}
                            </td>
                            <td class="px-2 py-1 flex items-center justify-self-center">
                                <img v-if="blog.image" :src="getImageUrl(blog.image)"
                                    class="w-10 h-10 object-cover rounded" :alt="blog.title" />
                                <div v-else class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center">
                                    <span class="text-gray-400">No Image</span>
                                </div>
                            </td>
                            <td class="px-2 py-1 font-medium text-center">
                                {{ blog.title }}
                            </td>
                            <td class="px-2 py-1 max-w-xs truncate">
                                {{ blog.description }}
                            </td>
                            <td class="px-2 py-1 text-center">
                                {{ blog.author?.username || 'Unknown' }}
                            </td>
                            <td class="px-2 py-1 text-center">
                                <button
                                    :class="['w-10 h-6 rounded-full relative transition-colors', blog.status === 1 ? 'bg-primary' : 'bg-gray-300']"
                                    @click="toggleStatus(blog)" :aria-pressed="blog.status === 1"
                                    style="background-color: #3bb77e">
                                    <span
                                        :class="['absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform', blog.status === 1 ? 'translate-x-4' : '']"></span>
                                </button>
                            </td>
                            <td class="px-2 py-1 text-center">
                                {{ formatDate(blog.created_at) }}
                            </td>
                            <td class="px-2 py-1">
                                <div class="flex text-center gap-2">
                                    <button @click="handleEdit(blog)"
                                        class="inline-flex items-center p-1.5 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors duration-150"
                                        title="Chỉnh sửa bài viết">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button @click="handleDelete(blog)"
                                        class="inline-flex items-center p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors duration-150"
                                        title="Xóa bài viết">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="blogs.length === 0">
                            <td colspan="8" class="px-3 py-2 text-center text-gray-500">
                                Không có dữ liệu
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination" class="flex justify-between items-center mt-6">
            <div class="text-sm text-gray-600">
                Hiển thị {{ blogs.length }} trong tổng số {{ pagination.total }} bài viết
            </div>
            <div class="flex gap-2">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                    class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button v-for="page in pagination.last_page" :key="page" @click="changePage(page)"
                    :class="{ 'bg-gray-500 text-white': pagination.current_page === page }"
                    class="px-3 py-1 border border-gray-300 rounded text-sm ">
                    {{ page }}
                </button>
                <button @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50">
                    <i class="fas fa-chevron-right"></i>
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
            return 'bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs'
        case 'archived':
            return 'bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-xs'
        case 'draft':
            return 'bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs'
        default:
            return 'bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs'
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

const toggleStatus = async (blog) => {
    const newStatus = blog.status === 1 ? 0 : 1
    try {
        await updateBlogStatus(blog.id, newStatus)
        blog.status = newStatus
        // Nếu có notyf hoặc emit refresh thì gọi ở đây
    } catch (e) {
        // Nếu có notyf thì báo lỗi ở đây
    }
}

// Hàm giả lập gọi API cập nhật trạng thái
const updateBlogStatus = async (id, status) => {
    // TODO: Thay bằng gọi API thực tế
    return new Promise((resolve) => setTimeout(resolve, 500))
}
</script>