<template>
    <div class="categories-page">
        <div class="page-header tw-flex tw-justify-between tw-items-center">
            <div>
                <h1>Quản lý danh mục</h1>
                <p class="text-gray-600">Quản lý danh mục sản phẩm của bạn</p>
            </div>
            <NuxtLink to="/admin/categories/create"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-primary-dark">
                <i class="fas fa-plus"></i>
                Thêm danh mục
            </NuxtLink>
        </div>

        <CategoriesTable :categories="categories" @delete="handleDelete" />
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})
import { useCategory } from '~/composables/useCategory'
import Swal from 'sweetalert2'
import CategoriesTable from '~/components/admin/categories/CategoriesTable.vue'

const { getCategories, deleteCategory } = useCategory()
const categories = ref([])

const handleDelete = async (category) => {
    try {
        await deleteCategory(category.id)
        categories.value = await getCategories()
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        })
        Toast.fire({
            icon: 'success',
            title: 'Danh mục đã được xóa thành công'
        })
    } catch (error) {
        console.error('Failed to delete category:', error)
        Swal.fire('Có lỗi xảy ra khi xóa danh mục', error.message, 'error')
    }
}

onMounted(async () => {
    try {
        categories.value = await getCategories()
        console.log(categories.value)
    } catch (error) {
        console.error('Failed to fetch categories:', error)
    }
})
</script>

<style scoped>
.categories-page {
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