<template>
    <div class="rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Thêm khuyến mãi mới</h1>
                <p class="text-gray-600">Điền thông tin để tạo chương trình khuyến mãi mới</p>
            </div>
        </div>

        <CouponForm />
    </div>
</template>

<script setup>
import CouponForm from '../../../components/admin/coupons/CouponsForm.vue';
import { useHead } from '@vueuse/head';
useHead({
    title: "Tạo mã khuyến mãi | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Tạo mã khuyến mãi"
        }
    ]
})
</script>
