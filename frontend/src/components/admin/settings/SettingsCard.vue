<template>
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <h2 class="text-lg font-semibold mb-4">{{ title }}</h2>
        <Form :fields="fields" :initial-data="modelValue" :model-value="modelValue"
            @update:modelValue="$emit('update:modelValue', $event)" />
    </div>
</template>

<script setup>
defineProps({
    title: String,
    fields: Array,
    modelValue: Object
})

defineEmits(['update:modelValue'])

import Form from './Form.vue'
</script>
