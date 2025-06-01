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

        <BrandsTable :brands="brands" @delete="handleDelete" />
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})
import { useBrand } from '~/composables/useBrand'
import Swal from 'sweetalert2'
import BrandsTable from '~/components/admin/brands/BrandsTable.vue'

const { getBrands, deleteBrand } = useBrand()
const brands = ref([])

const handleDelete = async (brand) => {
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