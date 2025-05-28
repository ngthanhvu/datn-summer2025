<template>
  <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
    <!-- Filters section -->
    <div v-if="showFilters" class="tw-flex tw-gap-4 tw-mb-4">
      <select v-if="categories.length" v-model="selectedCategory" class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56">
        <option value="">All category</option>
        <option v-for="category in categories" :key="category.value" :value="category.value">
          {{ category.label }}
        </option>
      </select>
      
      <input 
        v-if="showDateFilter"
        v-model="selectedDate"
        type="text" 
        class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56" 
        placeholder="dd/mm/yyyy" 
      />
      
      <select v-if="statuses.length" v-model="selectedStatus" class="tw-border tw-rounded tw-px-4 tw-py-2 tw-w-56">
        <option value="">All status</option>
        <option v-for="status in statuses" :key="status.value" :value="status.value">
          {{ status.label }}
        </option>
      </select>
    </div>

    <!-- Table section -->
    <div class="tw-overflow-x-auto">
      <table class="tw-w-full tw-text-left tw-bg-white">
        <thead>
          <tr class="tw-border-b tw-bg-gray-50">
            <th v-if="selectable" class="tw-px-4 tw-py-3">
              <input 
                type="checkbox" 
                :checked="allSelected"
                @change="toggleSelectAll"
              />
            </th>
            <th v-for="column in columns" :key="column.key" class="tw-px-4 tw-py-3">
              {{ column.label }}
            </th>
            <th v-if="showActions" class="tw-px-4 tw-py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, idx) in items" :key="idx" class="tw-border-b hover:tw-bg-gray-50">
            <td v-if="selectable" class="tw-px-4 tw-py-3">
              <input 
                type="checkbox" 
                v-model="selectedItems" 
                :value="item"
              />
            </td>
            <td 
              v-for="column in columns" 
              :key="column.key" 
              class="tw-px-4 tw-py-3"
              :class="column.class"
            >
              <!-- Image column -->
              <template v-if="column.type === 'image'">
                <img 
                  :src="item[column.key]" 
                  class="tw-w-14 tw-h-14 tw-object-cover tw-rounded" 
                  :alt="item[column.altKey || 'name']"
                />
              </template>
              
              <!-- Status column -->
              <template v-else-if="column.type === 'status'">
                <span :class="badgeClass(item[column.key])">
                  {{ item[column.labelKey || 'statusLabel'] }}
                </span>
              </template>
              
              <!-- Price column -->
              <template v-else-if="column.type === 'price'">
                {{ column.prefix || '$' }}{{ item[column.key] }}
              </template>
              
              <!-- Default column -->
              <template v-else>
                {{ item[column.key] }}
              </template>
            </td>
            
            <!-- Actions column -->
            <td v-if="showActions" class="tw-px-4 tw-py-3 tw-flex tw-gap-2">
              <slot name="actions" :item="item">
                <button 
                  v-if="showEditButton"
                  @click="$emit('edit', item)"
                  class="tw-bg-teal-600 tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-teal-700"
                >
                  Edit
                </button>
                <button 
                  v-if="showDeleteButton"
                  @click="$emit('delete', item)"
                  class="tw-border tw-border-gray-300 tw-rounded tw-px-4 tw-py-2 tw-bg-white hover:tw-bg-gray-100"
                >
                  Delete
                </button>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Props definition
const props = defineProps({
  // Data
  items: {
    type: Array,
    required: true,
    default: () => []
  },
  columns: {
    type: Array,
    required: true,
    default: () => []
  },
  
  // Filters
  showFilters: {
    type: Boolean,
    default: false
  },
  categories: {
    type: Array,
    default: () => []
  },
  statuses: {
    type: Array,
    default: () => []
  },
  showDateFilter: {
    type: Boolean,
    default: false
  },
  
  // Selection
  selectable: {
    type: Boolean,
    default: false
  },
  
  // Actions
  showActions: {
    type: Boolean,
    default: true
  },
  showEditButton: {
    type: Boolean,
    default: true
  },
  showDeleteButton: {
    type: Boolean,
    default: true
  }
})

// Emits
const emit = defineEmits(['edit', 'delete', 'selection-change', 'filter-change'])

// State
const selectedItems = ref([])
const selectedCategory = ref('')
const selectedStatus = ref('')
const selectedDate = ref('')

// Computed
const allSelected = computed(() => {
  return props.items.length > 0 && selectedItems.value.length === props.items.length
})

// Methods
function toggleSelectAll(event) {
  selectedItems.value = event.target.checked ? [...props.items] : []
  emit('selection-change', selectedItems.value)
}

// Watch for filter changes
watch([selectedCategory, selectedStatus, selectedDate], () => {
  emit('filter-change', {
    category: selectedCategory.value,
    status: selectedStatus.value,
    date: selectedDate.value
  })
})

function badgeClass(status) {
  switch (status) {
    case 'active':
      return 'tw-bg-green-100 tw-text-green-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
    case 'archived':
      return 'tw-bg-orange-100 tw-text-orange-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
    case 'disabled':
      return 'tw-bg-red-100 tw-text-red-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
    default:
      return 'tw-bg-gray-100 tw-text-gray-700 tw-px-3 tw-py-1 tw-rounded-full tw-text-xs'
  }
}
</script>

<style scoped></style>