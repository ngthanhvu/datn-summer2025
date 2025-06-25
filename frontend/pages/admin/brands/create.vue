<template>
    <BrandForm ref="brandForm" title="Thêm thương hiệu mới" description="Điền thông tin để tạo thương hiệu mới"
        submitText="Tạo thương hiệu" @submit="handleSubmit" />
</template>

<script setup>
useHead({
    title: 'Thêm thương hiệu'
})
definePageMeta({
    layout: 'admin',
    middleware: 'admin',
})

import { ref, onMounted } from 'vue'
import BrandForm from '~/components/admin/brands/BrandForm.vue'
const notyf = useNuxtApp().$notyf

const { getBrands, createBrand } = useBrand()
const brandForm = ref(null)

onMounted(async () => {
    try {
        const brands = await getBrands()
        if (brands && brandForm.value) {
            brandForm.value.formFields[2].options = brands.map(brand => ({
                label: brand.name,
                value: brand.id.toString()
            }))
        }
    } catch (error) {
        console.error('Error fetching brands:', error)
        notyf.error('Không thể tải danh sách thương hiệu')
    }
})

const handleSubmit = async (formData) => {
    try {
        const result = await createBrand(formData)

        if (result) {
            notyf.success('Tạo thương hiệu thành công')
            await navigateTo('/admin/brands')
        }
    } catch (error) {
        console.error('Error creating brand:', error)
        const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi tạo thương hiệu'
        console.log(errorMessage)
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