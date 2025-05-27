<template>
    <tr class="tw-border-b tw-border-gray-200">
        <td class="tw-py-4 tw-w-[40%]">
            <div class="tw-flex tw-items-center tw-gap-4">
                <img :src="product.image" :alt="product.name" class="tw-w-20 tw-h-20 tw-object-cover tw-rounded-md" />
                <div>
                    <p class="tw-font-bold tw-text-base tw-text-black tw-mb-2">{{ product.name }}</p>
                    <span :class="[
                        'tw-inline-block tw-text-xs tw-px-2 tw-py-1 tw-rounded-full tw-mb-2',
                        brandClasses[product.brand]
                    ]">{{ product.brand }}</span>
                </div>
            </div>
        </td>
        <td class="tw-py-4 tw-w-[20%]">
            <div class="tw-flex tw-justify-center tw-items-center">
                <button aria-label="Decrease quantity"
                    class="tw-text-[#6b7280] tw-text-xl tw-select-none hover:tw-text-black tw-transition-colors tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-full hover:tw-bg-gray-100"
                    type="button" @click="$emit('decrease')">
                    <i class="fas fa-minus"></i>
                </button>
                <input type="number" :value="quantity" min="1"
                    class="tw-w-16 tw-h-8 tw-text-center tw-border tw-border-gray-300 tw-mx-2 tw-text-sm focus:tw-outline-none focus:tw-border-blue-500"
                    @input="$emit('update:quantity', $event.target.value)" />
                <button aria-label="Increase quantity"
                    class="tw-text-[#6b7280] tw-text-xl tw-select-none hover:tw-text-black tw-transition-colors tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-rounded-full hover:tw-bg-gray-100"
                    type="button" @click="$emit('increase')">
                    <i class="fas fa-plus"></i>
                </button>
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

const brandClasses = {
    'Apple': 'tw-bg-red-100 tw-text-red-600',
    'Xiaomi': 'tw-bg-orange-100 tw-text-orange-600'
}

const formatPrice = (price) => {
    return `$${price.toFixed(2)}`
}

defineEmits(['remove', 'decrease', 'increase', 'update:quantity'])
</script>