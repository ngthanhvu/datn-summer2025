<template>
    <aside class="bg-white rounded-lg shadow-sm border border-gray-300">
        <!-- Mobile Menu -->
        <div class="lg:hidden p-4">
            <select :value="selected" @change="$emit('select', $event.target.value)"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-[#81AACC] focus:border-[#81AACC]">
                <option class="py-2" v-for="item in items" :key="item.key" :value="item.key">
                    {{ item.label }}
                </option>
            </select>
        </div>

        <!-- Desktop Menu -->
        <nav class="hidden lg:flex flex-col p-4 w-64">
            <button v-for="item in items" :key="item.key" @click="$emit('select', item.key)" :class="[
                'text-left px-4 py-3 rounded-lg font-medium transition-colors duration-200 mt-1 cursor-pointer',
                selected === item.key
                    ? 'bg-[#81AACC] text-white shadow-sm'
                    : 'bg-transparent text-gray-700 hover:bg-gray-50 hover:text-gray-900'
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
    { key: 'coupon', label: 'Mã giảm giá' },
]
</script>