<template>
    <div v-if="show"
        class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-items-center tw-justify-center tw-z-50">
        <div class="tw-bg-white tw-rounded-lg tw-p-6 tw-w-[600px]">
            <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                <h3 class="tw-text-lg tw-font-semibold">Chi tiết biến thể sản phẩm</h3>
                <button @click="$emit('close')" class="tw-text-gray-500 hover:tw-text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="tw-space-y-4">
                <div v-for="(variant, index) in variants" :key="index"
                    class="tw-p-4 tw-border tw-rounded-lg tw-space-y-2">
                    <div class="tw-flex tw-justify-between tw-items-center">
                        <div class="tw-space-y-1">
                            <div class="tw-flex tw-gap-2 tw-items-center">
                                <span v-if="variant.color" class="tw-w-6 tw-h-6 tw-rounded-full tw-border"
                                    :style="{ backgroundColor: variant.color }">
                                </span>
                                <span class="tw-font-medium">{{ variant.name || 'Biến thể ' + (index + 1) }}</span>
                            </div>
                            <div class="tw-text-sm tw-text-gray-600">
                                <div>Kích thước: {{ variant.size || 'N/A' }}</div>
                                <div>Màu sắc: {{ variant.colorName || variant.color || 'N/A' }}</div>
                                <div>Số lượng: {{ variant.stock || 0 }}</div>
                                <div>Giá: {{ formatPrice(variant.price) }}</div>
                            </div>
                        </div>
                        <div class="tw-flex tw-gap-2">
                            <button class="tw-p-2 tw-text-blue-600 hover:tw-text-blue-800">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="tw-p-2 tw-text-red-600 hover:tw-text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tw-mt-6 tw-flex tw-justify-end tw-gap-2">
                <button @click="$emit('close')" class="tw-px-4 tw-py-2 tw-border tw-rounded-lg hover:tw-bg-gray-50">
                    Đóng
                </button>
                <button class="tw-px-4 tw-py-2 tw-bg-primary tw-text-white tw-rounded-lg hover:tw-bg-primary-dark">
                    Thêm biến thể
                </button>
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
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}
</style>