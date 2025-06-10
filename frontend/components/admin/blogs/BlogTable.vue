<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <!-- Filters section -->
        <div class="tw-flex tw-gap-4 tw-mb-4">
            <select v-model="selectedCategory" class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56">
                <option value="">Tất cả danh mục</option>
                <option value="Photography">Photography</option>
                <option value="Architecture">Architecture</option>
                <option value="Technology">Technology</option>
            </select>

            <input v-model="selectedDate" type="text" class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56"
                placeholder="dd/mm/yyyy" />

            <select v-model="selectedStatus" class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56">
                <option value="">Tất cả trạng thái</option>
                <option value="active">Hoạt động</option>
                <option value="archived">Lưu trữ</option>
                <option value="disabled">Vô hiệu hóa</option>
            </select>
        </div>

        <!-- Table section -->
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-left tw-bg-white">
                <thead>
                    <tr class="tw-border-b tw-bg-gray-50">
                        <th class="tw-px-4 tw-py-3">
                            <input type="checkbox" :checked="allSelected" @change="toggleSelectAll" />
                        </th>
                        <th class="tw-px-4 tw-py-3">Hình ảnh</th>
                        <th class="tw-px-4 tw-py-3">Tên bài viết</th>
                        <th class="tw-px-4 tw-py-3">Mô tả</th>
                        <th class="tw-px-4 tw-py-3">Danh mục</th>
                        <th class="tw-px-4 tw-py-3">Trạng thái</th>
                        <th class="tw-px-4 tw-py-3">Lượt xem</th>
                        <th class="tw-px-4 tw-py-3">Ngày tạo</th>
                        <th class="tw-px-4 tw-py-3">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(blog, idx) in filteredBlogs" :key="idx" class="tw-border-b hover:tw-bg-gray-50">
                        <td class="tw-px-4 tw-py-3">
                            <input type="checkbox" v-model="selectedItems" :value="blog" />
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <img :src="blog.image" class="tw-w-14 tw-h-14 tw-object-cover tw-rounded"
                                :alt="blog.name" />
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-font-medium">
                            {{ blog.name }}
                        </td>
                        <td class="tw-px-4 tw-py-3 tw-max-w-xs tw-truncate">
                            {{ blog.description }}
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            {{ blog.category }}
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            <span :class="getStatusBadgeClass(blog.status)">
                                {{ blog.statusLabel }}
                            </span>
                        </td>
                        <td class="tw-px-4 tw-py-3">
                            {{ blog.views }}
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
        <div class="tw-flex tw-justify-between tw-items-center tw-mt-4">
            <div class="tw-text-sm tw-text-gray-500">
                Hiển thị {{ filteredBlogs.length }} trong tổng số {{ blogs.length }} bài viết
            </div>
            <div class="tw-flex tw-gap-2">
                <button class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm hover:tw-bg-gray-50">Trước</button>
                <button class="tw-px-3 tw-py-1 tw-bg-blue-500 tw-text-white tw-rounded tw-text-sm">1</button>
                <button class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm hover:tw-bg-gray-50">2</button>
                <button class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm hover:tw-bg-gray-50">Sau</button>
            </div>
        </div>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin',
    middleware: 'admin',
})
useHead({
    title: "Quản lý bài viết"
})
import { ref, computed } from 'vue'

// Sample blog data
const blogs = ref([
    {
        id: 1,
        name: '10 Tips for Capturing Stunning Landscape Photos',
        description: 'Landscape photography is one of the most popular genres among photographers.',
        image: 'https://storage.googleapis.com/a1aa/image/0937c7ad-97ec-45f0-c69c-cdba4f61b684.jpg',
        category: 'Photography',
        status: 'active',
        statusLabel: 'Hoạt động',
        created_at: '2023-05-25',
        views: 1234,
        tags: 'photography, landscape, tips'
    },
    {
        id: 2,
        name: 'How Technology is Changing Architecture',
        description: 'Macro photography is a fascinating genre that allows you to capture intricate details.',
        image: 'https://storage.googleapis.com/a1aa/image/43aedf66-1620-4716-0b93-d93fec7c29d9.jpg',
        category: 'Architecture',
        status: 'active',
        statusLabel: 'Hoạt động',
        created_at: '2023-06-25',
        views: 856,
        tags: 'architecture, technology, innovation'
    },
    {
        id: 3,
        name: 'The Art of Vintage Photography',
        description: 'Explore how to create timeless vintage-style photos with simple techniques.',
        image: 'https://storage.googleapis.com/a1aa/image/f34a288c-b3e0-4585-bac4-cb25a220aa98.jpg',
        category: 'Photography',
        status: 'archived',
        statusLabel: 'Lưu trữ',
        created_at: '2024-03-05',
        views: 432,
        tags: 'vintage, photography, art'
    },
    {
        id: 4,
        name: 'Sustainable Architecture Innovations',
        description: 'Learn about the latest trends and innovations driving sustainability in modern architecture.',
        image: 'https://storage.googleapis.com/a1aa/image/4530f9e6-43ba-4f0f-3f85-d59f15aedcd3.jpg',
        category: 'Architecture',
        status: 'disabled',
        statusLabel: 'Vô hiệu hóa',
        created_at: '2024-02-10',
        views: 678,
        tags: 'architecture, sustainability, green'
    }
])

// Filter states
const selectedCategory = ref('')
const selectedStatus = ref('')
const selectedDate = ref('')
const selectedItems = ref([])

// Computed properties
const allSelected = computed(() => {
    return filteredBlogs.value.length > 0 && selectedItems.value.length === filteredBlogs.value.length
})

const filteredBlogs = computed(() => {
    let filtered = blogs.value

    if (selectedCategory.value) {
        filtered = filtered.filter(blog => blog.category === selectedCategory.value)
    }

    if (selectedStatus.value) {
        filtered = filtered.filter(blog => blog.status === selectedStatus.value)
    }

    if (selectedDate.value) {
        filtered = filtered.filter(blog => blog.created_at.includes(selectedDate.value))
    }

    return filtered
})

// Methods
function toggleSelectAll(event) {
    selectedItems.value = event.target.checked ? [...filteredBlogs.value] : []
}

function handleEdit(blog) {
    console.log('Edit blog:', blog)
    navigateTo(`/admin/blogs/${blog.id}/edit`)
}

function handleDelete(blog) {
    if (confirm(`Bạn có chắc chắn muốn xóa bài viết "${blog.name}"?`)) {
        const index = blogs.value.findIndex(b => b.id === blog.id)
        if (index > -1) {
            blogs.value.splice(index, 1)
        }
    }
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'active':
            return 'tw-bg-green-100 tw-text-green-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'archived':
            return 'tw-bg-orange-100 tw-text-orange-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        case 'disabled':
            return 'tw-bg-red-100 tw-text-red-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
        default:
            return 'tw-bg-gray-100 tw-text-gray-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
    }
}

function formatDate(dateString) {
    const date = new Date(dateString)
    return date.toLocaleDateString('vi-VN')
}
</script>

<style></style>