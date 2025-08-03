<template>
    <div class="bg-[#F5F7FB] rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Chỉnh sửa sản phẩm</h1>
                <p class="text-gray-600">Cập nhật thông tin sản phẩm</p>
            </div>
            <router-link to="/admin/products"
                class="bg-gray-200 text-gray-600 rounded px-4 py-2 flex items-center gap-2 hover:bg-gray-300">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </router-link>
        </div>
        <ProductsEdit />
    </div>
</template>

<script setup>
import ProductsEdit from '../../../components/admin/products/ProductsEdit.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Sửa sản phẩm | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Sửa sản phẩm"
        }
    ]
})
</script>