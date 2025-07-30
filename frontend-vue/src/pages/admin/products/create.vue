<template>
    <div class="bg-[#F5F7FB] rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Thêm sản phẩm mới</h1>
                <p class="text-gray-600">Điền thông tin để tạo sản phẩm mới</p>
            </div>
            <router-link to="/admin/products"
                class="bg-gray-200 text-gray-600 rounded px-4 py-2 flex items-center gap-2 hover:bg-gray-300">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </router-link>
        </div>
        <ProductsForm />
    </div>
</template>

<script setup>
import ProductsForm from '../../../components/admin/products/ProductsForm.vue';
import { useHead } from '@vueuse/head'
useHead({
    title: 'Tạo sản phẩm',
    meta: [
        {
            name: "description",
            content: "Tạo sản phẩm"
        }
    ]
})
</script>

<style scoped>
.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
}
</style>