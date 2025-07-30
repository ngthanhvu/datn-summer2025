<template>
    <div v-if="show" class="fixed inset-0 bg-stone-950/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-[600px]">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Chi tiết biến thể sản phẩm</h3>
                <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700 cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div v-for="(variant, index) in variants" :key="index"
                    class="p-4 border border-gray-300 rounded-lg space-y-2">
                    <div class="flex justify-between items-center">
                        <div class="space-y-1">
                            <div class="flex gap-2 items-center">
                                <span v-if="variant.color" class="w-6 h-6 rounded-full border"
                                    :style="{ backgroundColor: variant.color }">
                                </span>
                                <span class="font-medium">{{ variant.name || 'Biến thể ' + (index + 1) }}</span>
                            </div>
                            <div class="text-sm text-gray-600">
                                <div>Kích thước: {{ variant.size || 'N/A' }}</div>
                                <div>Màu sắc: {{ variant.colorName || variant.color || 'N/A' }}</div>
                                <div>Giá: {{ formatPrice(variant.price) }}</div>
                            </div>
                        </div>
                        <!-- <div class="flex gap-2">
                            <button class="p-2 text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="p-2 text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    variants: {
        type: Array,
        required: true
    }
})

defineEmits(['close'])

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price || 0)
}
</script>

<style scoped>
.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
}
</style>