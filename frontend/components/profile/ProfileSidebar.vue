<template>
    <aside class="tw-bg-white tw-p-4 tw-rounded tw-shadow tw-w-64">
        <nav class="tw-flex tw-flex-col tw-gap-2">
            <button v-for="item in items" :key="item.key" @click="$emit('select', item.key)" :class="[
                'tw-text-left tw-px-4 tw-py-2 tw-rounded tw-font-medium',
                selected === item.key
                    ? 'tw-bg-[#81AACC] tw-text-white'
                    : 'tw-bg-transparent tw-text-gray-800 hover:tw-bg-gray-100'
            ]">
                {{ item.label }}
            </button>
        </nav>
    </aside>
</template>

<script setup>
const props = defineProps({
    selected: String
})
const items = [
    { key: 'info', label: 'Thông tin tài khoản' },
    { key: 'address', label: 'Địa chỉ' },
    { key: 'orders', label: 'Đơn hàng' },
]
</script>