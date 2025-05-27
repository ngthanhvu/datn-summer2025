<template>
  <div>
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
      <div>
        <h2 class="tw-text-3xl tw-font-bold">Products List</h2>
        <p class="tw-text-gray-400 tw-mt-1">Lorem ipsum dolor sit amet.</p>
      </div>
      <div class="tw-flex tw-gap-2">
        <button
          class="tw-border tw-border-gray-300 tw-rounded tw-px-4 tw-py-2 tw-bg-white hover:tw-bg-gray-100">Export</button>
        <button
          class="tw-border tw-border-gray-300 tw-rounded tw-px-4 tw-py-2 tw-bg-white hover:tw-bg-gray-100">Import</button>
        <button class="tw-bg-teal-600 tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-teal-700">Create new</button>
      </div>
    </div>
    <Table />
  </div>
</template>

<script setup>
import Table from '@/components/Table.vue';
definePageMeta({
  layout: 'admin'
})
</script>

<style></style>