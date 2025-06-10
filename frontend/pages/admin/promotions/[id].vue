<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Sửa khuyến mãi mới</h1>
                <p class="tw-text-gray-600">Điền thông tin để sửa chương trình khuyến mãi mới</p>
            </div>
            <NuxtLink to="/admin/promotions"
                class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </NuxtLink>
        </div>

        <AdminPromotionEditForm :couponId="$route.params.id" @cancel="$router.push('/admin/promotions')" @updated="$router.push('/admin/promotions')" />
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})
</script>

<style></style>