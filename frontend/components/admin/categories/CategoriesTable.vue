<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-4">
        <div class="tw-flex tw-justify-end tw-mb-4" v-if="categories.length > 0">
            <button v-if="selectedCategories.size > 0" @click="$emit('bulkDelete', selectedCategories)"
                class="tw-bg-red-500 tw-text-white tw-rounded tw-px-3 tw-py-1 hover:tw-bg-red-600 tw-flex tw-items-center tw-gap-2">
                <i class="fas fa-trash"></i>
                Xóa {{ selectedCategories.size }} mục đã chọn
            </button>
        </div>
        <div class="tw-overflow-x-auto">
            <table class="tw-w-full tw-text-sm">
                <thead>
                    <tr class="tw-border-b tw-bg-gray-50">
                        <th class="tw-px-3 tw-py-2 tw-text-center">
                            <div class="tw-flex tw-items-center">
                                <input type="checkbox" :checked="selectedCategories.size === categories.length"
                                    @change="toggleSelectAll" class="tw-rounded">
                            </div>
                        </th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">#</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Ảnh</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Tên danh mục</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Mô tả</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Danh mục cha</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Số lượng sản phẩm</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Trạng thái</th>
                        <th class="tw-px-3 tw-py-2 tw-text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Skeleton loading -->
                    <tr v-if="props.isLoading" v-for="n in 8" :key="'skeleton-' + n">
                        <td v-for="i in 8" :key="i" class="tw-px-3 tw-py-2">
                            <div class="skeleton-loader"></div>
                        </td>
                    </tr>
                    <tr v-else v-for="(category, index) in categories" :key="category.id"
                        class="tw-border-b hover:tw-bg-gray-50">
                        <td class="tw-px-3 tw-py-2">
                            <input type="checkbox" :checked="selectedCategories.has(category.id)"
                                @change="toggleSelect(category.id)" class="tw-rounded">
                        </td>
                        <td class="tw-px-3 tw-py-2 text-center">{{ index + 1 }}</td>
                        <td class="tw-px-3 tw-py-2 text-center">
                            <img :src="category.image" :alt="category.name"
                                class="tw-w-8 tw-h-8 tw-object-cover tw-rounded">
                        </td>
                        <td class="tw-px-3 tw-py-2 text-center">{{ category.name }}</td>
                        <td class="tw-px-3 tw-py-2 text-center">{{ category.description }}</td>
                        <td class="tw-px-3 tw-py-2 text-center">{{category.parent_id ? categories.find(c => c.id ===
                            category.parent_id).name : 'Không có danh mục cha'}}</td>
                        <td class="tw-px-3 tw-py-2 text-center">{{ category.products_count }}</td>
                        <td class="tw-px-3 tw-py-2 text-center">
                            <button
                                :class="['tw-w-10 tw-h-6 tw-rounded-full tw-relative tw-transition-colors', Number(category.is_active) === 1 ? 'tw-bg-primary' : 'tw-bg-gray-300']"
                                @click="toggleStatus(category)" :aria-pressed="Number(category.is_active) === 1"
                                style="background-color: #3bb77e">
                                <span
                                    :class="['tw-absolute tw-top-0.5 tw-left-0.5 tw-w-5 tw-h-5 tw-bg-white tw-rounded-full tw-shadow tw-transition-transform', Number(category.is_active) === 1 ? 'tw-translate-x-4' : '']"></span>
                            </button>
                        </td>
                        <td class="tw-px-3 tw-py-2 text-center">
                            <div class="tw-flex tw-items-center tw-justify-center tw-gap-2">
                                <NuxtLink :to="`/admin/categories/${category.id}/edit`"
                                    class="tw-inline-flex tw-items-center tw-p-1.5 tw-text-blue-600 hover:tw-text-blue-900 hover:tw-bg-blue-50 tw-rounded-lg tw-transition-colors tw-duration-150"
                                    title="Chỉnh sửa danh mục">
                                    <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </NuxtLink>
                                <button @click="handleDelete(category)"
                                    class="tw-inline-flex tw-items-center tw-p-1.5 tw-text-red-600 hover:tw-text-red-900 hover:tw-bg-red-50 tw-rounded-lg tw-transition-colors tw-duration-150"
                                    title="Xóa danh mục">
                                    <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!props.isLoading && categories.length === 0">
                        <td colspan="8" class="tw-px-3 tw-py-2 tw-text-center tw-text-gray-500">
                            Không có dữ liệu
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import Swal from 'sweetalert2'

const props = defineProps({
    categories: {
        type: Array,
        required: true
    },
    isLoading: {
        type: Boolean,
        default: false
    }
})

const selectedCategories = ref(new Set())

const toggleSelectAll = (event) => {
    if (event.target.checked) {
        selectedCategories.value = new Set(props.categories.map(category => category.id))
    } else {
        selectedCategories.value.clear()
    }
}

const toggleSelect = (categoryId) => {
    if (selectedCategories.value.has(categoryId)) {
        selectedCategories.value.delete(categoryId)
    } else {
        selectedCategories.value.add(categoryId)
    }
}

const emit = defineEmits(['delete', 'bulkDelete'])

const handleDelete = async (category) => {
    Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa danh mục?',
        text: `Bạn có chắc chắn muốn xóa danh mục "${category.name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Xóa',
    }).then(async (result) => {
        if (result.isConfirmed) {
            emit('delete', category)
        }
    })
}

const toggleStatus = async (category) => {
    const newStatus = Number(category.is_active) === 1 ? 0 : 1
    try {
        await updateCategoryStatus(category.id, newStatus)
        category.is_active = newStatus
        // Nếu có notyf hoặc emit refresh thì gọi ở đây
    } catch (e) {
        // Nếu có notyf thì báo lỗi ở đây
    }
}

// Hàm giả lập gọi API cập nhật trạng thái
const updateCategoryStatus = async (id, status) => {
    // TODO: Thay bằng gọi API thực tế
    return new Promise((resolve) => setTimeout(resolve, 500))
}
</script>

<style scoped>
.skeleton-loader {
    height: 20px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
    border-radius: 4px;
    animation: skeleton-loading 3.2s infinite;
}

@keyframes skeleton-loading {
    0% {
        background-position: -200px 0;
    }

    100% {
        background-position: calc(200px + 100%) 0;
    }
}
</style>