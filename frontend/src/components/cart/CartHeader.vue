<template>
    <header class="flex justify-between items-center mb-4">
        <h2 class="font-semibold text-[25px] text-black">Giỏ hàng</h2>
        <span class="font-semibold text-base text-black">{{ itemCount }} sản phẩm</span>
    </header>

    <hr class="border-t border-gray-300 mb-4" />

    <div class="overflow-x-auto">
        <table class="w-full min-w-[800px]">
            <thead>
                <tr class="text-sm text-gray-500 uppercase font-semibold">
                    <th class="text-left pb-2">Sản phẩm</th>
                    <th class="text-center pb-2">Số lượng</th>
                    <th class="text-center pb-2">Giá</th>
                    <th class="text-center pb-2">Tổng</th>
                    <th class="w-20"></th>
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
