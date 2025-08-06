<template>
    <div class="rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Cập nhật mã khuyến mãi</h1>
                <p class="text-gray-600 mt-1">Cập nhật thông tin khuyến mãi</p>
            </div>
        </div>
        <CouponsEdit />
    </div>
</template>

<script setup>
import CouponsEdit from '../../../components/admin/coupons/CouponsEdit.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Cập nhật mã giảm giá | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Cập nhật mã giảm giá"
        }
    ]
})
</script>