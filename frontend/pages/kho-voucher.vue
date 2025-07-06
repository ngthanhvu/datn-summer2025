<template>
    <div class="tw-container tw-mx-auto tw-px-4 tw-py-6">
        <VoucherList />
    </div>
</template>

<script setup>
import VoucherList from '~/components/voucher/VoucherList.vue'

// SEO
useHead({
    title: 'Kho Voucher - DEVGANG',
    meta: [
        { name: 'description', content: 'Khám phá và sử dụng các mã giảm giá hấp dẫn tại DEVGANG' },
    ],
})
</script>
