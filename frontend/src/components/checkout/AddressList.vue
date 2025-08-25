<template>
    <div class="mb-6">
        <h2 class="text-lg font-semibold mb-4">Địa chỉ giao hàng</h2>
        <div class="space-y-4">
            <div v-for="(address, index) in addresses" :key="index"
                class="p-4 border-2 rounded-md cursor-pointer transition-all duration-200" :class="selectedAddress === index
                    ? 'border-[#81AACC] bg-blue-50 shadow-md'
                    : 'border-gray-300 hover:bg-gray-50 hover:border-gray-400'" @click="$emit('select', index)">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <p class="font-medium"
                                :class="selectedAddress === index ? 'text-[#81AACC]' : 'text-gray-900'">
                                {{ address.fullName }}
                            </p>
                            <span v-if="selectedAddress === index"
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-[#81AACC] text-white">
                                <i class="fas fa-check mr-1"></i>
                                Đã chọn
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">{{ address.phone }}</p>
                        <p class="text-sm text-gray-600">{{ address.fullAddress }}</p>
                    </div>
                    <div class="flex gap-2 ml-4">
                        <button @click.stop="$emit('edit', index)"
                            class="text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-100 transition-colors">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button @click.stop="$emit('delete', index)" :disabled="isLoading"
                            class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <div v-if="isLoading"
                                class="animate-spin rounded-full h-4 w-4 border-2 border-red-600 border-t-transparent">
                            </div>
                            <i v-else class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <button @click="$emit('add')"
                class="w-full p-3 border-2 border-dashed border-gray-300 rounded-md text-gray-600 hover:border-gray-400 hover:text-gray-800 cursor-pointer transition-all duration-200 hover:bg-gray-50">
                <i class="fas fa-plus mr-2"></i>
                Thêm địa chỉ mới
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    addresses: {
        type: Array,
        required: true
    },
    selectedAddress: {
        type: Number,
        required: true
    },
    isLoading: {
        type: Boolean,
        default: false
    }
})

defineEmits(['select', 'edit', 'delete', 'add'])
</script>