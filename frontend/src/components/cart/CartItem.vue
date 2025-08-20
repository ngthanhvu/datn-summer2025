<template>
    <tr class="border-b border-gray-200">
        <td class="py-4 w-[40%]">
            <div class="flex items-center gap-4">
                <img :src="getImageUrl(product?.variant?.product?.main_image?.image_path)"
                    :alt="product?.variant?.product?.name || 'Product image'"
                    class="w-20 h-20 object-cover rounded-md" />
                <div>
                    <p class="font-bold text-base text-black mb-2">{{ product?.variant?.product?.name }}</p>
                    <span v-if="product?.variant?.product?.brand"
                        class="inline-block text-xs px-2 py-1 rounded-full mb-2 bg-blue-100 text-blue-600">
                        {{ product.variant.product.brand.name }}
                    </span>
                    <div class="text-xs text-gray-500">
                        <span v-if="product.variant.color">Màu: {{ product.variant.color }}</span>
                        <span v-if="product.variant.size" class="ml-2">Size: {{ product.variant.size }}</span>
                    </div>
                </div>
            </div>
        </td>
        <td class="py-4 w-[20%]">
            <div class="flex flex-col items-center gap-2">
                <div class="flex justify-center items-center">
                    <button aria-label="Decrease quantity"
                        class="text-gray-500 text-xl select-none hover:text-black transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 cursor-pointer"
                        type="button" @click="handleDecrease" :disabled="quantity <= 1">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="text" v-model="localQuantity" inputmode="numeric" pattern="[0-9]*"
                        class="w-16 h-8 text-center border border-gray-300 mx-2 text-sm focus:outline-none focus:border-blue-500"
                        @keyup.enter="commitQuantity" @blur="commitQuantity" />
                    <button aria-label="Increase quantity"
                        class="text-gray-500 text-xl select-none hover:text-black transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 cursor-pointer"
                        type="button" @click="handleIncrease"
                        :disabled="quantity >= maxAvailable">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <!-- Error messages -->
                <p v-if="error || externalError" class="text-red-500 text-xs">{{ error || externalError }}</p>
                <!-- Stock warning -->
                <p v-if="isOverStock" class="text-orange-600 text-xs font-semibold">
                    ⚠️ Vượt quá tồn kho ({{ maxAvailable }})
                </p>
            </div>
        </td>
        <td class="py-4 text-center text-base font-semibold text-black w-[15%]">
            {{ formatPrice(product.price) }}
        </td>
        <td class="py-4 text-center text-base font-semibold text-black w-[15%]">
            {{ formatPrice(product.price * quantity) }}
        </td>
        <td class="py-4 text-center w-[10%]">
            <button class="text-sm text-gray-400 hover:text-red-500 transition-colors cursor-pointer"
                @click="$emit('remove')">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
    </tr>
</template>

<script setup>
import { ref, watch, computed } from 'vue'

const props = defineProps({
    product: { type: Object, required: true },
    quantity: { type: Number, required: true },
    maxAvailable: { type: Number, default: 999 },
    externalError: { type: String, default: '' }
})

const emit = defineEmits(['remove', 'decrease', 'increase', 'update:quantity'])
const error = ref('')
const localQuantity = ref(String(props.quantity))

// Computed property để kiểm tra xem có vượt quá tồn kho không
const isOverStock = computed(() => {
    return props.quantity > props.maxAvailable
})

watch(() => props.quantity, (val) => {
    localQuantity.value = String(val)
})

const BASE_URL = import.meta.env.VITE_API_BASE_URL

const commitQuantity = () => {
    const raw = (localQuantity.value ?? '').toString().trim()
    const maxquantity = props.maxAvailable
    const parsed = parseInt(raw, 10)

    if (!raw) {
        localQuantity.value = String(props.quantity)
        return
    }

    if (isNaN(parsed) || parsed < 1) {
        error.value = 'Số lượng tối thiểu là 1'
        localQuantity.value = '1'
        emit('update:quantity', 1)
        return
    }

    if (parsed > maxquantity) {
        error.value = `Số lượng tối đa còn lại là ${maxquantity}`
        localQuantity.value = String(maxquantity)
        emit('update:quantity', maxquantity)
        return
    }

    error.value = ''
    emit('update:quantity', parsed)
}

const handleIncrease = () => {
    const maxquantity = props.maxAvailable
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

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price)
}

const getImageUrl = (path) => {
    if (!path) return '/default-image.jpg'
    if (path.startsWith('http://') || path.startsWith('https://')) return path
    if (path.startsWith('/storage/')) return BASE_URL + path
    if (path.startsWith('storage/')) return BASE_URL + '/' + path
    return BASE_URL + '/' + path
}
</script>
