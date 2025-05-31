<template>
  <div class="products-page">
    <div class="page-header">
      <h1>Quản lý sản phẩm</h1>
      <p class="text-gray-600">Quản lý danh sách sản phẩm của bạn</p>
    </div>

    <Table :columns="columns" :data="products" :create-route="'/admin/products/create'" :edit-route="'/admin/products'"
      @delete="handleDelete" />
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

import { ref, onMounted } from 'vue'
import Table from '~/components/admin/Table.vue'
import { useProducts } from '~/composables/useProducts'

// Table columns configuration
const columns = [
  { key: 'id', label: 'ID' },
  { key: 'main_image', label: 'Ảnh chính', type: 'main_image' },
  { key: 'sub_images', label: 'Ảnh phụ', type: 'sub_images' },
  { key: 'name', label: 'Tên sản phẩm' },
  { key: 'price', label: 'Giá gốc', type: 'price' },
  { key: 'discount_price', label: 'Giá khuyến mãi', type: 'price' },
  { key: 'quantity', label: 'Số lượng' },
  { key: 'variants', label: 'Biến thể', type: 'variants' },
  { key: 'is_active', label: 'Trạng thái', type: 'status' }
]

const products = ref([])
const { getProducts, deleteProduct } = useProducts()

// Fetch products on component mount
onMounted(async () => {
  try {
    const data = await getProducts()
    products.value = data
  } catch (error) {
    console.error('Error fetching products:', error)
  }
})

// Handlers
const handleDelete = async (product) => {
  if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
    try {
      await deleteProduct(product.id)
      const index = products.value.findIndex(p => p.id === product.id)
      if (index !== -1) {
        products.value.splice(index, 1)
      }
    } catch (error) {
      console.error('Error deleting product:', error)
    }
  }
}
</script>

<style scoped>
.products-page {
  padding: 1.5rem;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 1.875rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.5rem;
}
</style>