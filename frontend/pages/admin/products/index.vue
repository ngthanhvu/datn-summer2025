<template>
  <div>
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
      <div>
        <h2 class="tw-text-3xl tw-font-bold">Products List</h2>
        <p class="tw-text-gray-400 tw-mt-1">Manage your products inventory</p>
      </div>
      <div class="tw-flex tw-gap-2">
        <button class="tw-border tw-border-gray-300 tw-rounded tw-px-4 tw-py-2 tw-bg-white hover:tw-bg-gray-100"
          @click="handleExport">
          Export
        </button>
        <button class="tw-border tw-border-gray-300 tw-rounded tw-px-4 tw-py-2 tw-bg-white hover:tw-bg-gray-100"
          @click="handleImport">
          Import
        </button>
        <button class="tw-bg-teal-600 tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-teal-700"
          @click="handleCreate">
          Create new
        </button>
      </div>
    </div>

    <Table :items="products" :columns="columns" :show-filters="true" :categories="categories" :statuses="statuses"
      :show-date-filter="true" :selectable="true" @edit="handleEdit" @delete="handleDelete"
      @selection-change="handleSelectionChange" @filter-change="handleFilterChange" />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Table from '@/components/Table.vue'

definePageMeta({
  layout: 'admin'
})

// Table columns configuration
const columns = [
  { key: 'image', type: 'image', label: '', altKey: 'name' },
  { key: 'name', label: 'Name', class: 'tw-font-medium' },
  { key: 'price', type: 'price', label: 'Price', class: 'tw-text-blue-900' },
  { key: 'status', type: 'status', label: 'Status', labelKey: 'statusLabel' },
  { key: 'date', label: 'Date' }
]

// Filter options
const categories = [
  { value: 'clothing', label: 'Clothing' },
  { value: 'accessories', label: 'Accessories' },
  { value: 'electronics', label: 'Electronics' },
  { value: 'furniture', label: 'Furniture' }
]

const statuses = [
  { value: 'active', label: 'Active' },
  { value: 'archived', label: 'Archived' },
  { value: 'disabled', label: 'Disabled' }
]

// Sample products data (in real app, this would come from an API)
const products = ref([
  {
    image: 'https://placehold.co/60x60',
    name: 'T-shirt for men medium size',
    price: '34.50',
    status: 'active',
    statusLabel: 'Active',
    date: '02.11.2022',
    category: 'clothing'
  },
  {
    image: 'https://placehold.co/60x60',
    name: 'Helionic Hooded Down Jacket',
    price: '990.99',
    status: 'active',
    statusLabel: 'Active',
    date: '02.11.2022',
    category: 'clothing'
  },
  {
    image: 'https://placehold.co/60x60',
    name: 'Lace mini dress with faux leather',
    price: '76.99',
    status: 'archived',
    statusLabel: 'Archived',
    date: '02.11.2022',
    category: 'clothing'
  },
  {
    image: 'https://placehold.co/60x60',
    name: "Fanmis Men's Travel Bag",
    price: '18.00',
    status: 'active',
    statusLabel: 'Active',
    date: '02.11.2022',
    category: 'accessories'
  },
  {
    image: 'https://placehold.co/60x60',
    name: 'Modern Armchair for home interiors',
    price: '76.99',
    status: 'active',
    statusLabel: 'Active',
    date: '02.11.2022',
    category: 'furniture'
  }
])

// Event handlers
function handleEdit(item) {
  console.log('Edit item:', item)
  // Navigate to edit page or open edit modal
}

function handleDelete(item) {
  console.log('Delete item:', item)
  // Show confirmation dialog and delete item
}

function handleSelectionChange(selectedItems) {
  console.log('Selected items:', selectedItems)
  // Handle bulk actions
}

function handleFilterChange(filters) {
  console.log('Filter changed:', filters)
  // Apply filters to products list
}

function handleExport() {
  console.log('Export products')
  // Export products to CSV/Excel
}

function handleImport() {
  console.log('Import products')
  // Show import dialog
}

function handleCreate() {
  console.log('Create new product')
  // Navigate to create page or open create modal
}
</script>

<style></style>