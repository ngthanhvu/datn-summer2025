<template>
    <div class="flex flex-wrap gap-2 items-center">
        <!-- Color variants -->
        <span v-for="variant in uniqueColors" :key="variant.color"
            class="flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-gray-100">
            <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: variant.color }"></span>
            {{ variant.colorName }}
        </span>

        <!-- Size variants -->
        <span v-for="size in uniqueSizes" :key="size"
            class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
            {{ size }}
        </span>

        <!-- More button -->
        <button @click="showModal = true"
            class="p-1 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 cursor-pointer">
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

const uniqueSizes = computed(() => {
    return [...new Set(props.variants
        .filter(v => v.size)
        .map(v => v.size))]
})
</script>