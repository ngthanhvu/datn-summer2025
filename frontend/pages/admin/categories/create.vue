<template>
    <CategoryForm title="Thêm danh mục mới" description="Điền thông tin để tạo danh mục mới" submitText="Tạo danh mục"
        @submit="handleSubmit" />
</template>

<script setup>
useHead({
    title: 'Thêm danh mục'
})
definePageMeta({
    layout: 'admin',
    middleware: 'admin',
})

import { ref, onMounted } from 'vue'
import CategoryForm from '~/components/admin/categories/CategoryForm.vue'

const { getCategories, createCategory } = useCategory()
const categoryForm = ref(null)

onMounted(async () => {
    try {
        const categories = await getCategories()
        if (categories && categoryForm.value) {
            categoryForm.value.formFields[2].options = categories.map(cat => ({
                label: cat.name,
                value: cat.id.toString()
            }))
        }
    } catch (error) {
        console.error('Error fetching categories:', error)
        alert('Không thể tải danh sách danh mục')
    }
})

const handleSubmit = async (formData) => {
    try {
        const result = await createCategory(formData)

        if (result) {
            alert('Tạo danh mục thành công')
            await navigateTo('/admin/categories')
        }
    } catch (error) {
        console.error('Error creating category:', error)
        const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi tạo danh mục'
        alert(errorMessage)
    }
}
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}
</style>