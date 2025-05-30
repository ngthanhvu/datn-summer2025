<template>
    <div class="brands-page">
        <div class="page-header tw-flex tw-justify-between tw-items-center">
            <div>
                <h1>Quản lý thương hiệu</h1>
                <p class="text-gray-600">Quản lý thương hiệu sản phẩm của bạn</p>
            </div>
            <NuxtLink to="/admin/brands/create"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-primary-dark">
                <i class="fas fa-plus"></i>
                Thêm thương hiệu
            </NuxtLink>
        </div>

        <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
            <div class="tw-overflow-x-auto">
                <table class="tw-w-full">
                    <thead>
                        <tr class="tw-border-b tw-bg-gray-50">
                            <th class="tw-px-4 tw-py-3 tw-text-left">ID</th>
                            <th class="tw-px-4 tw-py-3 tw-text-left">Logo</th>
                            <th class="tw-px-4 tw-py-3 tw-text-left">Tên thương hiệu</th>
                            <th class="tw-px-4 tw-py-3 tw-text-left">Mô tả</th>
                            <th class="tw-px-4 tw-py-3 tw-text-left">Trạng thái</th>
                            <th class="tw-px-4 tw-py-3 tw-text-left">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="brand in brands" :key="brand.id" class="tw-border-b hover:tw-bg-gray-50">
                            <td class="tw-px-4 tw-py-3">#{{ brand.id }}</td>
                            <td class="tw-px-4 tw-py-3">
                                <img :src="brand.image" :alt="brand.name"
                                    class="tw-w-10 tw-h-10 tw-object-cover tw-rounded">
                            </td>
                            <td class="tw-px-4 tw-py-3">{{ brand.name }}</td>
                            <td class="tw-px-4 tw-py-3">{{ brand.description }}</td>
                            <td class="tw-px-4 tw-py-3">
                                <span :class="[
                                    'tw-px-2 tw-py-1 tw-rounded-full tw-text-xs',
                                    Number(brand.is_active) === 1 ? 'tw-bg-green-100 tw-text-green-700' : 'tw-bg-red-100 tw-text-red-700'
                                ]">
                                    {{ Number(brand.is_active) === 1 ? 'Hoạt động' : 'Vô hiệu' }}
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
                        <tr v-if="brands.length === 0">
                            <td colspan="6" class="tw-py-8">
                                <div class="tw-text-center tw-text-gray-500">
                                    <i class="fas fa-box-open tw-text-4xl tw-mb-3"></i>
                                    <p class="tw-text-lg">Không có thương hiệu nào</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})
import { useBrand } from '~/composables/useBrand'
import Swal from 'sweetalert2'

const { getBrands, deleteBrand } = useBrand()
const brands = ref([])

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
            try {
                await deleteBrand(brand.id)
                brands.value = await getBrands()
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                })
                Toast.fire({
                    icon: 'success',
                    title: 'Thương hiệu đã được xóa thành công'
                })
            } catch (error) {
                console.error('Failed to delete brand:', error)
                Swal.fire('Có lỗi xảy ra khi xóa thương hiệu', error.message, 'error')
            }
        }
    })
}

onMounted(async () => {
    try {
        brands.value = await getBrands()
        console.log(brands.value)
    } catch (error) {
        console.error('Failed to fetch brands:', error)
    }
})
</script>

<style scoped>
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

.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}
</style>