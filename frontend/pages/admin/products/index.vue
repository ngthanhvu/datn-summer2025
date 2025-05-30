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

import { ref } from 'vue'
import Table from '~/components/admin/Table.vue'

// Table columns configuration
const columns = [
  { key: 'id', label: 'ID' },
  { key: 'image', label: 'Hình ảnh', type: 'image' },
  { key: 'name', label: 'Tên sản phẩm' },
  { key: 'category', label: 'Danh mục' },
  { key: 'brand', label: 'Thương hiệu' },
  { key: 'variants', label: 'Biến thể', type: 'variants' },
  { key: 'price', label: 'Giá', type: 'price' },
  { key: 'status', label: 'Trạng thái', type: 'status' }
]

// Mock data with variants
const products = ref([
  {
    id: 1,
    image: 'https://via.placeholder.com/150',
    name: 'iPhone 13 Pro Max',
    category: 'phone',
    brand: 'apple',
    price: 30990000,
    status: true,
    description: 'iPhone 13 Pro Max 128GB',
    variants: [
      {
        name: 'iPhone 13 Pro Max Xanh 128GB',
        color: '#2E3A59',
        colorName: 'Xanh Sierra',
        size: '128GB',
        price: 30990000,
        stock: 10
      },
      {
        name: 'iPhone 13 Pro Max Vàng 256GB',
        color: '#FFD700',
        colorName: 'Vàng',
        size: '256GB',
        price: 33990000,
        stock: 5
      },
      {
        name: 'iPhone 13 Pro Max Bạc 512GB',
        color: '#C0C0C0',
        colorName: 'Bạc',
        size: '512GB',
        price: 39990000,
        stock: 3
      }
    ]
  },
  {
    id: 2,
    image: 'https://via.placeholder.com/150',
    name: 'Samsung Galaxy S21',
    category: 'phone',
    brand: 'samsung',
    price: 20990000,
    status: true,
    description: 'Samsung Galaxy S21 Ultra 5G',
    variants: [
      {
        name: 'Galaxy S21 Đen 256GB',
        color: '#000000',
        colorName: 'Đen',
        size: '256GB',
        price: 20990000,
        stock: 8
      },
      {
        name: 'Galaxy S21 Bạc 256GB',
        color: '#C0C0C0',
        colorName: 'Bạc',
        size: '256GB',
        price: 20990000,
        stock: 6
      }
    ]
  },
  {
    id: 3,
    image: 'https://via.placeholder.com/150',
    name: 'MacBook Pro M1',
    category: 'laptop',
    brand: 'apple',
    price: 35990000,
    status: false,
    description: 'MacBook Pro M1 13 inch',
    variants: [
      {
        name: 'MacBook Pro M1 8GB/256GB',
        size: '8GB RAM',
        price: 35990000,
        stock: 4
      },
      {
        name: 'MacBook Pro M1 16GB/512GB',
        size: '16GB RAM',
        price: 41990000,
        stock: 2
      }
    ]
  },
  {
    id: 4,
    image: 'https://via.placeholder.com/150',
    name: 'iPad Pro 2021',
    category: 'tablet',
    brand: 'apple',
    price: 23990000,
    status: true,
    description: 'iPad Pro 12.9 inch 2021',
    variants: [
      {
        name: 'iPad Pro WiFi 128GB',
        size: '128GB',
        price: 23990000,
        stock: 7
      },
      {
        name: 'iPad Pro 5G 256GB',
        size: '256GB',
        price: 27990000,
        stock: 4
      }
    ]
  }
])

// Handlers
const handleDelete = (product) => {
  if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
    const index = products.value.findIndex(p => p.id === product.id)
    if (index !== -1) {
      products.value.splice(index, 1)
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