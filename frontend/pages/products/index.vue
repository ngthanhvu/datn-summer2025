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
import { ref, onMounted } from 'vue'
import { useProducts } from '~/composables/useProducts'

const showFilter = ref(false)
const products = ref([])
const { getProducts } = useProducts()

// Xử lý sự kiện khi người dùng thay đổi bộ lọc
const handleFilter = async (filters) => {
  try {
    // Gọi API với các tham số lọc
    products.value = await getProducts(filters)
  } catch (error) {
    console.error('Error fetching products:', error)
  }
}

// Lấy tất cả sản phẩm khi component được tạo
onMounted(async () => {
  try {
    products.value = await getProducts()
  } catch (error) {
    console.error('Error fetching initial products:', error)
  }
})
</script>