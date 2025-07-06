<template>
    <div class="tw-rounded-lg tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Thêm khuyến mãi mới</h1>
                <p class="tw-text-gray-600">Điền thông tin để tạo chương trình khuyến mãi mới</p>
            </div>
        </div>

        <PromotionForm />
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import PromotionForm from '~/components/admin/promotion/PromotionForm.vue'
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}
</style>