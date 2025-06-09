<template>
    <tr class="tw-border-b tw-border-gray-200">
        <td class="tw-py-4 tw-w-[40%]">
            <div class="tw-flex tw-items-center tw-gap-4">
                <img :src="runtimeConfig.public.apiBaseUrl + product?.variant?.product?.main_image?.image_path"
                    :alt="product?.variant?.product?.name || 'Product image'"
                    class="tw-w-20 tw-h-20 tw-object-cover tw-rounded-md" />
                <div>
                    <p class="tw-font-bold tw-text-base tw-text-black tw-mb-2">{{ product?.variant?.product?.name }}</p>
                    <span v-if="product?.variant?.product?.brand"
                        class="tw-inline-block tw-text-xs tw-px-2 tw-py-1 tw-rounded-full tw-mb-2 tw-bg-blue-100 tw-text-blue-600">{{
                            product.variant.product.brand.name }}</span>
                    <div class="tw-text-xs tw-text-gray-500">
                        <span v-if="product.variant.color">Màu: {{ product.variant.color }}</span>
                        <span v-if="product.variant.size" class="tw-ml-2">Size: {{ product.variant.size }}</span>
                    </div>
                </div>
            </div>
        </td>
        <td class="tw-py-4 tw-w-[20%]">
            <div class="tw-flex tw-flex-col tw-items-center tw-gap-2">
                <div class="tw-flex tw-justify-center tw-items-center">
                    <button aria-label="Decrease quantity"
                        class="tw-text-[#6b7280] tw-text-xl tw-select-none hover:tw-text-black tw-transition-colors tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-full hover:tw-bg-gray-100"
                        type="button" 
                        @click="handleDecrease"
                        :disabled="quantity <= 1">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" 
                        :value="quantity" 
                        min="1"
                        :max="product?.variant?.quantity || 999"
                        class="tw-w-16 tw-h-8 tw-text-center tw-border tw-border-gray-300 tw-mx-2 tw-text-sm focus:tw-outline-none focus:tw-border-blue-500"
                        @input="handleQuantityInput" />
                    <button aria-label="Increase quantity"
                        class="tw-text-[#6b7280] tw-text-xl tw-select-none hover:tw-text-black tw-transition-colors tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-full hover:tw-bg-gray-100"
                        type="button" 
                        @click="handleIncrease"
                        :disabled="quantity >= (product?.variant?.quantity || 999)">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <p v-if="error" class="tw-text-red-500 tw-text-xs">{{ error }}</p>
            </div>
        </td>
        <td class="tw-py-4 tw-text-center tw-text-base tw-font-semibold tw-text-black tw-w-[15%]">
            {{ formatPrice(product.price) }}
        </td>
        <td class="tw-py-4 tw-text-center tw-text-base tw-font-semibold tw-text-black tw-w-[15%]">
            {{ formatPrice(product.price * quantity) }}
        </td>
        <td class="tw-py-4 tw-text-center tw-w-[10%]">
            <button class="tw-text-sm tw-text-[#9ca3af] hover:tw-text-red-500 tw-transition-colors"
                @click="$emit('remove')">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
    </tr>
</template>

<script setup>
import { ref } from 'vue'
import { useNuxtApp } from '#app'

const { $config: runtimeConfig } = useNuxtApp()

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    quantity: {
        type: Number,
        required: true
    }
})

const error = ref('')

const handleQuantityInput = (event) => {
    const newValue = parseInt(event.target.value) || 1
    const maxquantity = props.product?.variant?.quantity || 999
    
    if (newValue < 1) {
        error.value = 'Số lượng không được nhỏ hơn 1'
        emit('update:quantity', 1)
    } else if (newValue > maxquantity) {
        error.value = `Số lượng tồn kho chỉ còn ${maxquantity}`
        emit('update:quantity', maxquantity)
    } else {
        error.value = ''
        emit('update:quantity', newValue)
    }
}

const handleIncrease = () => {
    const maxquantity = props.product?.variant?.quantity || 999
    if (props.quantity < maxquantity) {
        error.value = ''
        emit('increase')
    } else {
        error.value = `Số lượng tồn kho chỉ còn ${maxquantity}`
    }
}

const handleDecrease = () => {
    if (props.quantity > 1) {
        error.value = ''
        emit('decrease')
    }
}

const emit = defineEmits(['remove', 'decrease', 'increase', 'update:quantity'])

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}
</script>