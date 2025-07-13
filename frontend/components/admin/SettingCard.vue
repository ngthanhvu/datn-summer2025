<template>
  <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
    <h2 class="tw-text-lg tw-font-semibold tw-mb-4">{{ title }}</h2>
    <Form
      :fields="fields"
      :initial-data="modelValue"
      :model-value="modelValue"
      @update:modelValue="$emit('update:modelValue', $event)"
    />
  </div>
</template>

<script setup>
defineProps({
  title: String,
  fields: Array,
  modelValue: Object
})

defineEmits(['update:modelValue'])

import Form from '~/components/admin/Form.vue'
</script>
