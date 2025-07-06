<template>
    <div class="tw-prose tw-max-w-none">
        <h3 class="tw-text-xl tw-font-bold tw-mb-4">Mô tả sản phẩm</h3>
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