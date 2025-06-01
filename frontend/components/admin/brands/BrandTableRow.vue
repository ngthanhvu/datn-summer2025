<template>
    <tr class="tw-border-b hover:tw-bg-gray-50">
        <td class="tw-px-4 tw-py-3">#{{ brand.id }}</td>
        <td class="tw-px-4 tw-py-3">
            <img :src="brand.image" :alt="brand.name" class="tw-w-10 tw-h-10 tw-object-cover tw-rounded">
        </td>
        <td class="tw-px-4 tw-py-3">{{ brand.name }}</td>
        <td class="tw-px-4 tw-py-3">{{ brand.description }}</td>
        <td class="tw-px-4 tw-py-3">{{ getParentBrandName(brand) }}</td>
        <td class="tw-px-4 tw-py-3">
            <span :class="getStatusClass(brand.is_active)">
                {{ getStatusText(brand.is_active) }}
            </span>
        </td>
        <td class="tw-px-4 tw-py-3">
            <div class="tw-flex tw-gap-2">
                <NuxtLink :to="`/admin/brands/${brand.id}/edit`"
                    class="tw-bg-blue-500 tw-text-white tw-rounded tw-px-2 tw-py-1 hover:tw-bg-blue-600">
                    <i class="fas fa-edit"></i>
                </NuxtLink>
                <button @click="handleDelete(brand)"
                    class="tw-bg-red-500 tw-text-white tw-rounded tw-px-2 tw-py-1 hover:tw-bg-red-600">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
</template>

<script setup>
import Swal from 'sweetalert2'

const props = defineProps({
    brand: {
        type: Object,
        required: true
    },
    brands: {
        type: Array,
        required: true
    }
})

const emit = defineEmits(['delete'])

const getParentBrandName = (brand) => {
    if (!brand.parent_id) return 'Không có thương hiệu cha'
    const parentBrand = props.brands.find(b => b.id === brand.parent_id)
    return parentBrand ? parentBrand.name : 'Không có thương hiệu cha'
}

const getStatusClass = (isActive) => {
    return [
        'tw-px-2 tw-py-1 tw-rounded-full tw-text-xs',
        Number(isActive) === 1 ? 'tw-bg-green-100 tw-text-green-700' : 'tw-bg-red-100 tw-text-red-700'
    ]
}

const getStatusText = (isActive) => {
    return Number(isActive) === 1 ? 'Hoạt động' : 'Vô hiệu'
}

const handleDelete = async (brand) => {
    Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa thương hiệu?',
        text: `Bạn có chắc chắn muốn xóa thương hiệu "${brand.name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Xóa',
    }).then(async (result) => {
        if (result.isConfirmed) {
            emit('delete', brand)
        }
    })
}
</script>