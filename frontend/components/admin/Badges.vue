<template>
    <div class="tw-flex tw-flex-wrap tw-gap-2 tw-items-center">
        <!-- Color variants -->
        <span v-for="variant in uniqueColors" :key="variant.color"
            class="tw-flex tw-items-center tw-gap-1 tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-full tw-bg-gray-100">
            <span class="tw-w-3 tw-h-3 tw-rounded-full" :style="{ backgroundColor: variant.color }"></span>
            {{ variant.colorName }}
        </span>

        <!-- Size variants -->
        <span v-for="size in uniqueSizes" :key="size"
            class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-full tw-bg-blue-100 tw-text-blue-800">
            {{ size }}
        </span>

        <!-- More button -->
        <button @click="showModal = true"
            class="tw-p-1 tw-text-gray-500 hover:tw-text-gray-700 tw-rounded-full hover:tw-bg-gray-100">
            <i class="fas fa-ellipsis-h"></i>
        </button>

        <!-- Variant Modal -->
        <VariantModal :show="showModal" :variants="variants" @close="showModal = false" />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import VariantModal from './VariantModal.vue'

const props = defineProps({
    variants: {
        type: Array,
        required: true
    }
})

const showModal = ref(false)

// Get unique colors
const uniqueColors = computed(() => {
    const colors = new Map()
    props.variants.forEach(variant => {
        if (variant.color && !colors.has(variant.color)) {
            colors.set(variant.color, {
                color: variant.color,
                colorName: variant.colorName || variant.color
            })
        }
    })
    return Array.from(colors.values())
})

// Get unique sizes
const uniqueSizes = computed(() => {
    return [...new Set(props.variants
        .filter(v => v.size)
        .map(v => v.size))]
})
</script>