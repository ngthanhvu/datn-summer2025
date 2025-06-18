<template>
    <div class="tw-mt-8">
        <h2 class="tw-text-lg tw-font-semibold tw-mb-4">Phương thức thanh toán</h2>
        <div class="tw-space-y-4">
            <div v-for="(method, index) in methods" :key="index"
                class="tw-flex tw-items-center tw-p-3 tw-border tw-rounded-md tw-cursor-pointer hover:tw-bg-gray-50"
                :class="{ 'tw-border-[#81AACC]': selectedMethod === index }" @click="$emit('select', index)">
                <input type="radio" :name="name" class="tw-mr-3" :checked="selectedMethod === index">
                <img v-if="method.image" :src="method.image" :alt="method.title"
                    class="tw-w-[25px] tw-h-[25px] tw-object-contain tw-mr-3" />
                <div>
                    <div class="tw-font-medium">{{ method.title }}</div>
                    <div class="tw-text-sm tw-text-gray-500">{{ method.description }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    methods: {
        type: Array,
        required: true
    },
    selectedMethod: {
        type: Number,
        required: true
    },
    name: {
        type: String,
        default: 'payment'
    }
})

defineEmits(['select'])
</script>