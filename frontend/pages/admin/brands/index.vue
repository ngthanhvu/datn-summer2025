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

        <BrandsTable :brands="brands" :isLoading="isLoading" @delete="handleDelete" @bulk-delete="handleBulkDelete" />
    </div>
</template>

<script setup>
useHead({
    title: "Quản lý thương hiệu",
    meta: [
        { name: "description", content: "Quản lý thương hiệu sản phẩm của bạn" }
    ]
})
definePageMeta({
    layout: 'admin',
    middleware: 'admin'
})
import { useBrand } from '@/composables/useBrand'
import Swal from 'sweetalert2'
import BrandsTable from '@/components/admin/brands/BrandTable.vue'

const { getBrands, deleteBrand, bulkDeleteBrands } = useBrand()
const brands = ref([])
const isLoading = ref(true)

const handleDelete = async (brand) => {
    try {
        await deleteBrand(brand.id)
        isLoading.value = true
        brands.value = await getBrands()
        isLoading.value = false
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
        isLoading.value = false
        console.error('Failed to delete brand:', error)
        Swal.fire('Có lỗi xảy ra khi xóa thương hiệu', error.message, 'error')
    }
}

const handleBulkDelete = async (selectedBrands) => {
    try {
        const result = await Swal.fire({
            title: 'Xác nhận xóa hàng loạt',
            text: `Bạn có chắc chắn muốn xóa ${selectedBrands.size} thương hiệu đã chọn?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        })

        if (result.isConfirmed) {
            isLoading.value = true
            await bulkDeleteBrands(selectedBrands)
            brands.value = await getBrands()
            isLoading.value = false

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            })

            Toast.fire({
                icon: 'success',
                title: 'Đã xóa thành công các thương hiệu đã chọn'
            })
        }
    } catch (error) {
        isLoading.value = false
        console.error('Failed to bulk delete brands:', error)
        Swal.fire('Có lỗi xảy ra khi xóa thương hiệu', error.message, 'error')
    }
}

onMounted(async () => {
    isLoading.value = true
    try {
        brands.value = await getBrands()
    } catch (error) {
        console.error('Failed to fetch brands:', error)
    } finally {
        isLoading.value = false
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