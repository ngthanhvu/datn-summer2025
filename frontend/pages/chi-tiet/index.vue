<template>
  <div class="tw-flex tw-gap-6">
    <!-- Sidebar filter -->
    <ProductFilter
      v-model="showFilter"
      @filter="handleFilter"
    />

    <!-- Danh sách sản phẩm -->
    <div class="tw-flex-1">
      <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-4">
        <!-- Hiển thị sản phẩm ở đây -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useProducts } from '~/composables/useProducts'

const showFilter = ref(false)
const products = ref([])
const { getProducts } = useProducts()
const route = useRoute()

const getAllChildIds = (categories, parentId) => {
  const children = categories.filter(cat => cat.parent_id === parentId)
  let ids = [parentId]
  for (const child of children) {
    ids = ids.concat(getAllChildIds(categories, child.id))
  }
  return ids
}

const handleFilter = async (filters) => {
  try {
    products.value = await getProducts(filters)
  } catch (error) {
    console.error('Error fetching products:', error)
  }
}

const fetchProducts = async () => {
  try {
    const filters = {}
    if (route.query.category) {
      filters.category = route.query.category
    }
    if (route.query.brand) {
      filters.brand = route.query.brand
    }
    products.value = await getProducts(filters)
  } catch (error) {
    console.error('Error fetching products:', error)
  }
}

onMounted(fetchProducts)
watch([() => route.query.category, () => route.query.brand], fetchProducts)
</script>