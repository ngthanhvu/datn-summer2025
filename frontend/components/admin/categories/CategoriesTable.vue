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
                        <th class="tw-px-3 tw-py-2 tw-text-left">
                            <div class="tw-flex tw-items-center">
                                <input type="checkbox" :checked="selectedCategories.size === categories.length"
                                    @change="toggleSelectAll" class="tw-rounded">
                            </div>
                        </th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">ID</th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">Ảnh</th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">Tên danh mục</th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">Mô tả</th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">Danh mục cha</th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">Trạng thái</th>
                        <th class="tw-px-3 tw-py-2 tw-text-left">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="category in categories" :key="category.id" class="tw-border-b hover:tw-bg-gray-50">
                        <td class="tw-px-3 tw-py-2">
                            <input type="checkbox" :checked="selectedCategories.has(category.id)"
                                @change="toggleSelect(category.id)" class="tw-rounded">
                        </td>
                        <td class="tw-px-3 tw-py-2">#{{ category.id }}</td>
                        <td class="tw-px-3 tw-py-2">
                            <img :src="category.image" :alt="category.name"
                                class="tw-w-8 tw-h-8 tw-object-cover tw-rounded">
                        </td>
                        <td class="tw-px-3 tw-py-2">{{ category.name }}</td>
                        <td class="tw-px-3 tw-py-2">{{ category.description }}</td>
                        <td class="tw-px-3 tw-py-2">{{category.parent_id ? categories.find(c => c.id ===
                            category.parent_id).name : 'Không có danh mục cha'}}</td>
                        <td class="tw-px-3 tw-py-2">
                            <span :class="[
                                'tw-px-2 tw-py-0.5 tw-rounded-full tw-text-xs',
                                Number(category.is_active) === 1 ? 'tw-bg-green-100 tw-text-green-700' : 'tw-bg-red-100 tw-text-red-700'
                            ]">
                                {{ Number(category.is_active) === 1 ? 'Hoạt động' : 'Vô hiệu' }}
                            </span>
                        </td>
                        <td class="tw-px-3 tw-py-2">
                            <div class="tw-flex tw-gap-1">
                                <NuxtLink :to="`/admin/categories/${category.id}/edit`"
                                    class="tw-bg-blue-500 tw-text-white tw-rounded tw-p-1.5 hover:tw-bg-blue-600">
                                    <i class="fas fa-edit"></i>
                                </NuxtLink>
                                <button @click="handleDelete(category)"
                                    class="tw-bg-red-500 tw-text-white tw-rounded tw-p-1.5 hover:tw-bg-red-600">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="categories.length === 0">
                        <td colspan="8" class="tw-py-4">
                            <div class="tw-text-center tw-text-gray-500">
                                <i class="fas fa-box-open tw-text-3xl tw-mb-2"></i>
                                <p class="tw-text-sm">Không có danh mục nào</p>
                            </div>
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
</script>