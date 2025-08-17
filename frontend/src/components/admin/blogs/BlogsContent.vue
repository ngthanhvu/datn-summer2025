<template>
    <div class="brands-page">
        <div class="page-header flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1>Quản lý bài viết</h1>
                <p class="text-gray-600">Quản lý bài viết và danh mục của bạn</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button @click="handleRefresh"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Tải lại
                </button>
                <router-link to="/admin/blogs/create"
                    class="w-full sm:w-auto bg-primary text-white rounded px-4 py-2 flex items-center justify-center gap-2 hover:bg-primary-dark cursor-pointer">
                    <i class="fas fa-plus"></i>
                    Thêm bài viết
                </router-link>
            </div>
        </div>

        <!-- Two column layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left column: Blog categories -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">Danh mục bài viết</h2>
                        <button @click="showCategoryModal = true"
                            class="bg-primary text-white rounded px-3 py-1 text-sm hover:bg-primary-dark cursor-pointer">
                            <i class="fas fa-plus mr-1"></i>
                            Thêm
                        </button>
                    </div>

                    <!-- Categories list -->
                    <div v-if="categoriesLoading" class="space-y-3">
                        <div v-for="n in 3" :key="n" class="bg-gray-200 h-12 rounded animate-pulse"></div>
                    </div>
                    <div v-else-if="categories.length === 0" class="text-center text-gray-500 py-8">
                        Chưa có danh mục nào
                    </div>
                    <div v-else class="space-y-2">
                        <div v-for="category in categories" :key="category.id"
                            class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <img v-if="category.image" :src="getImageUrl(category.image)"
                                    class="w-10 h-10 object-cover rounded" :alt="category.name" />
                                <div v-else class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center">
                                    <i class="fas fa-folder text-gray-400"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800">{{ category.name }}</div>
                                    <div class="text-sm text-gray-500">{{ category.blogs_count || 0 }} bài viết</div>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button @click="editCategory(category)"
                                    class="p-1 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded cursor-pointer">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button @click="deleteCategories(category)"
                                    class="p-1 text-red-600 hover:text-red-900 hover:bg-red-50 rounded cursor-pointer">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column: Blogs table -->
            <div class="lg:col-span-2">
                <BlogTable :blogs="blogs" :loading="loading" :error="error" :pagination="pagination"
                    @delete="deleteBlog" @refresh="handleRefresh" />
            </div>
        </div>

        <!-- Category Modal -->
        <div v-if="showCategoryModal"
            class="fixed inset-0 bg-gray-900/50 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-semibold mb-4">
                    {{ editingCategory ? 'Chỉnh sửa danh mục' : 'Thêm danh mục mới' }}
                </h3>
                <form @submit.prevent="handleCategorySubmit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên danh mục <span
                                class="text-red-500">*</span></label>
                        <input v-model="categoryForm.name" type="text" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-green-500"
                            placeholder="Nhập tên danh mục" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả <span
                                class="text-red-500">*</span></label>
                        <textarea v-model="categoryForm.description" rows="3"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-green-500"
                            placeholder="Nhập mô tả danh mục"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái <span
                                class="text-red-500">*</span></label>
                        <select v-model="categoryForm.status"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-green-500">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Không hoạt động</option>
                        </select>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="closeCategoryModal"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 cursor-pointer">
                            Hủy
                        </button>
                        <button type="submit"
                            class="flex-1 bg-primary text-white rounded px-4 py-2 hover:bg-primary-dark cursor-pointer">
                            {{ editingCategory ? 'Cập nhật' : 'Thêm mới' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import BlogTable from './BlogsTable.vue'
import { useBlog } from '../../../composable/useBlogs'
import Swal from 'sweetalert2'
import { push } from 'notivue'

const { blogs, loading, error, pagination, categories, categoriesLoading, fetchBlogs, deleteBlog, fetchCategories, createCategory, updateCategory, deleteCategory } = useBlog()

const showCategoryModal = ref(false)
const editingCategory = ref(null)
const categoryForm = ref({
    name: '',
    description: '',
    status: 'active'
})

const handleRefresh = async () => {
    await fetchBlogs()
}

const editCategory = (category) => {
    editingCategory.value = category
    categoryForm.value = {
        name: category.name,
        description: category.description || '',
        status: category.status
    }
    showCategoryModal.value = true
}

const closeCategoryModal = () => {
    showCategoryModal.value = false
    editingCategory.value = null
    categoryForm.value = {
        name: '',
        description: '',
        status: 'active'
    }
}

const handleCategorySubmit = async () => {
    try {
        if (editingCategory.value) {
            await updateCategory(editingCategory.value.id, categoryForm.value)
            push.success('Cập nhật danh mục thành công!')
        } else {
            await createCategory(categoryForm.value)
            push.success('Thêm danh mục thành công!')
        }
        closeCategoryModal()
    } catch (err) {
        console.error('Category submit error:', err)
    }
}

const deleteCategories = async (category) => {
    if (category.blogs_count > 0) {
        Swal.fire({
            title: 'Không thể xóa',
            text: 'Danh mục này đang có bài viết. Vui lòng xóa bài viết trước.',
            icon: 'warning'
        })
        return
    }

    const result = await Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa danh mục?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Xóa',
    })

    if (result.isConfirmed) {
        try {
            await deleteCategory(category.id)
            push.success('Đã xóa danh mục.')
        } catch (err) {
            console.error('Delete category error:', err)
        }
    }
}

const getImageUrl = (path) => {
    if (!path) return ''
    return path.startsWith('/storage/') ? `http://localhost:8000${path}` : path
}

onMounted(async () => {
    await Promise.all([fetchBlogs(), fetchCategories()])
})
</script>

<style>
.brands-page {
    padding: 1.5rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.875rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}

@media (max-width: 640px) {
    .page-header h1 {
        font-size: 1.5rem;
    }
}

.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
}
</style>