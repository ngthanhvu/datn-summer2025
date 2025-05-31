<template>
  <div class="products-page">
    <div class="page-header">
      <h1>Quản lý sản phẩm</h1>
      <p class="text-gray-600">Quản lý danh sách sản phẩm của bạn</p>
    </div>

    <ProductsTable :columns="columns" :data="products" :categories="categories" :brands="brands"
      @delete="handleDelete" />
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

import { ref, onMounted } from 'vue'
import ProductsTable from '~/components/admin/ProductsTable.vue'
import { useProducts } from '~/composables/useProducts'
import Swal from 'sweetalert2'

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'main_image', label: 'Ảnh chính', type: 'main_image' },
  { key: 'sub_images', label: 'Ảnh phụ', type: 'sub_images' },
  { key: 'name', label: 'Tên sản phẩm' },
  { key: 'category', label: 'Danh mục', type: 'category' },
  { key: 'brand', label: 'Thương hiệu', type: 'brand' },
  { key: 'price', label: 'Giá gốc', type: 'price' },
  { key: 'discount_price', label: 'Giá khuyến mãi', type: 'price' },
  { key: 'quantity', label: 'Số lượng' },
  { key: 'variants', label: 'Biến thể', type: 'variants' },
  { key: 'is_active', label: 'Trạng thái', type: 'status' }
]

const products = ref([])
const brands = ref([])
const categories = ref([])
const { getProducts, deleteProduct, getBrands, getCategories } = useProducts()

onMounted(async () => {
  try {
    const [productsData, brandsData, categoriesData] = await Promise.all([
      getProducts(),
      getBrands(),
      getCategories()
    ])

    products.value = productsData.map(product => ({
      ...product,
      brand: brandsData.find(b => b.id === product.brand_id)?.name || 'N/A',
      category: categoriesData.find(c => c.id === product.categories_id)?.name || 'N/A'
    }))

    brands.value = brandsData.map(brand => ({
      value: brand.name,
      label: brand.name
    }))

    categories.value = categoriesData.map(category => ({
      value: category.name,
      label: category.name
    }))
  } catch (error) {
    console.error('Error fetching data:', error)
  }
})

const handleDelete = async (product) => {
  Swal.fire({
    title: 'Bạn có chắc chắn muốn xóa sản phẩm này không?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await deleteProduct(product.id)
        products.value = await getProducts()
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
        })
        Toast.fire({
          icon: 'success',
          title: 'Sản phẩm đã được xóa thành công'
        })
      } catch (error) {
        console.error('Error deleting product:', error)
        Swal.fire('Có lỗi xảy ra khi xóa sản phẩm', error.message, 'error')
      }
    }
  })
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