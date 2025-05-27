<template>
    <header class="tw-flex tw-justify-between tw-items-center tw-mb-4">
        <h2 class="tw-font-semibold tw-text-[25px] tw-text-black">Giỏ hàng</h2>
        <span class="tw-font-semibold tw-text-base tw-text-black">{{ itemCount }} sản phẩm</span>
    </header>
    <hr class="tw-border-t tw-border-gray-300 tw-mb-4" />
    <div class="tw-overflow-x-auto">
        <table class="tw-w-full tw-min-w-[800px]">
            <thead>
                <tr class="tw-text-sm tw-text-[#6b7280] tw-uppercase tw-font-semibold">
                    <th class="tw-text-left tw-pb-2">Sản phẩm</th>
                    <th class="tw-text-center tw-pb-2">Số lượng</th>
                    <th class="tw-text-center tw-pb-2">Giá</th>
                    <th class="tw-text-center tw-pb-2">Tổng</th>
                    <th class="tw-w-20"></th>
                </tr>
            </thead>
        </table>
    </div>
</template>

<script setup>
defineProps({
    itemCount: {
        type: Number,
        required: true
    }
})
</script>