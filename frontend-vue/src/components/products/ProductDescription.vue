<template>
    <div class="prose max-w-none">
        <h3 class="text-xl font-bold mb-4">Mô tả sản phẩm</h3>
        <div v-html="description"></div>
    </div>
</template>

<script setup>
defineProps({
    description: {
        type: String,
        required: true
    }
})
</script>