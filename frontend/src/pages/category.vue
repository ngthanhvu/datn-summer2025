<template>
  <section class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 py-4 md:py-6">
    <!-- Hero/Breadcrumb -->
    <div class="rounded-xl bg-gradient-to-r from-[#81AACC]/20 to-[#3BB77E]/10 p-4 md:p-6 mb-6">
      <div class="flex items-center justify-between gap-4">
        <div>
          <nav class="text-sm text-gray-500 mb-2">
            <router-link to="/" class="hover:text-[#81AACC]">Trang chủ</router-link>
            <span class="mx-2">/</span>
            <span>Danh mục</span>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-medium">{{ categoryLabel }}</span>
          </nav>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">{{ categoryLabel }}</h1>
          <p class="text-gray-500 mt-1">{{ pagination.total || products.length }} sản phẩm</p>
        </div>
      </div>
    </div>

    <!-- Toolbar: sort + price filter -->
    <div class="bg-white rounded-xl shadow-sm p-3 md:p-4 mb-5 grid grid-cols-1 gap-3 md:flex md:items-center md:justify-between">
      <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
        <label class="text-sm text-gray-600">Sắp xếp:</label>
        <select v-model="sortBy" class="border border-gray-300 rounded-md px-3 py-2 text-sm w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-[#81AACC] focus:border-[#81AACC]">
          <option value="">Mặc định</option>
          <option value="price">Giá</option>
          <option value="created_at">Mới nhất</option>
        </select>
        <select v-model="sortDir" class="border border-gray-300 rounded-md px-3 py-2 text-sm w-full sm:w-auto focus:outline-none focus:ring-2 focus:ring-[#81AACC] focus:border-[#81AACC]">
          <option value="asc">Tăng dần</option>
          <option value="desc">Giảm dần</option>
        </select>
      </div>

      <div class="grid grid-cols-2 sm:flex items-center gap-2 sm:gap-3">
        <label class="text-sm text-gray-600 col-span-2 sm:col-auto">Khoảng giá:</label>
        <input v-model.number="minPrice" type="number" placeholder="Từ" @keyup.enter="applyFilters" class="border border-gray-300 rounded-md px-3 py-2 text-sm w-full sm:w-28 focus:outline-none focus:ring-2 focus:ring-[#81AACC] focus:border-[#81AACC]">
        <span class="hidden sm:block text-gray-400">—</span>
        <input v-model.number="maxPrice" type="number" placeholder="Đến" @keyup.enter="applyFilters" class="border border-gray-300 rounded-md px-3 py-2 text-sm w-full sm:w-28 focus:outline-none focus:ring-2 focus:ring-[#81AACC] focus:border-[#81AACC]">
        <button type="button" @click="applyFilters" class="col-span-2 sm:col-auto px-4 py-2 bg-[#81AACC] text-white rounded-lg text-sm hover:bg-[#6387A6] w-full sm:w-auto shadow-sm">Lọc</button>
        <button type="button" @click="resetFilters" class="col-span-2 sm:col-auto px-4 py-2 border border-gray-300 rounded-lg text-sm w-full sm:w-auto hover:bg-gray-50 shadow-sm">Đặt lại</button>
      </div>
    </div>

    <!-- Product grid / skeleton / empty -->
    <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div v-for="i in 8" :key="i" class="bg-white rounded-lg shadow-sm p-4 animate-pulse h-56"></div>
    </div>

    <div v-else-if="products.length === 0" class="text-center text-gray-500 py-16 bg-white rounded-xl shadow-sm">
      Không có sản phẩm nào trong danh mục này.
    </div>

    <div v-else
         class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
      <div v-for="p in products" :key="p.id" class="">
        <Card :product="p" @quick-view="openQuickView" />
      </div>
    </div>

    <QuickView :show="showQuickView" :product="quickViewProduct" @close="closeQuickView" />

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-2 mt-6 mb-4">
      <button :disabled="page<=1" @click="changePage(page-1)" class="px-3 py-1 border rounded-md disabled:opacity-50">Trước</button>
      <button v-for="p in pagesToShow" :key="p" @click="changePage(p)"
              :class="['px-3 py-1 rounded-md', p===page ? 'bg-[#81AACC] text-white' : 'border']">{{ p }}</button>
      <button :disabled="page>=pagination.last_page" @click="changePage(page+1)" class="px-3 py-1 border rounded-md disabled:opacity-50">Sau</button>
    </div>
  </section>
</template>

<script setup>
import { onMounted, ref, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProducts } from '../composable/useProducts'
import Card from '../components/ui/Card.vue'
import QuickView from '../components/products/Quick-view.vue'

const route = useRoute()
const router = useRouter()
const { getProducts, getCategories } = useProducts()

const loading = ref(false)
const products = ref([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const page = ref(1)
const sortBy = ref('')
const sortDir = ref('asc')
const minPrice = ref()
const maxPrice = ref()
const categoryLabel = ref('Danh mục')
const categories = ref([])

const formatPrice = (price) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price || 0)
const getImage = (p) => p?.main_image?.image_path || p?.images?.[0]?.image_path || '/product.png'

const fetchByCategory = async () => {
  const categoryKey = route.params.slug || route.query.category
  try {
    if (!categories.value.length) {
      categories.value = await getCategories()
    }
  } catch {}

  let resolvedId = categoryKey
  let resolvedName = String(categoryKey || '').toString()
  if (Array.isArray(categories.value)) {
    const match = categories.value.find(c => String(c.slug) === String(categoryKey) || String(c.id) === String(categoryKey))
    if (match) {
      resolvedId = match.id
      resolvedName = match.name || match.title || match.slug
    }
  }

  categoryLabel.value = resolvedName
  loading.value = true
  try {
    const res = await getProducts({
      category: resolvedId,
      sort_by: sortBy.value || undefined,
      sort_direction: sortDir.value,
      min_price: minPrice.value || undefined,
      max_price: maxPrice.value || undefined,
    }, page.value)
    products.value = res?.products || []
    pagination.value = res?.pagination || { current_page: 1, last_page: 1, total: products.value.length }
  } catch (e) {
    products.value = []
    pagination.value = { current_page: 1, last_page: 1, total: 0 }
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  page.value = 1
  updateQuery()
  fetchByCategory()
}
const resetFilters = () => {
  sortBy.value = ''
  sortDir.value = 'asc'
  minPrice.value = undefined
  maxPrice.value = undefined
  page.value = 1
  updateQuery()
  fetchByCategory()
}

const changePage = (p) => { page.value = p; updateQuery(); fetchByCategory() }

const pagesToShow = computed(() => {
  const total = pagination.value.last_page || 1
  const current = page.value
  const start = Math.max(1, current - 2)
  const end = Math.min(total, start + 4)
  return Array.from({ length: end - start + 1 }, (_, i) => start + i)
})

const updateQuery = () => {
  router.replace({ query: {
    category: route.params.slug || route.query.category,
    page: page.value,
    sort_by: sortBy.value || undefined,
    sort_direction: sortDir.value,
    min_price: minPrice.value || undefined,
    max_price: maxPrice.value || undefined,
  }})
}

onMounted(() => {
  page.value = Number(route.query.page || 1)
  sortBy.value = route.query.sort_by || ''
  sortDir.value = route.query.sort_direction || 'asc'
  minPrice.value = route.query.min_price ? Number(route.query.min_price) : undefined
  maxPrice.value = route.query.max_price ? Number(route.query.max_price) : undefined
  fetchByCategory()
})
watch(() => [route.params.slug], () => { page.value = 1; fetchByCategory() })

const showQuickView = ref(false)
const quickViewProduct = ref(null)
const openQuickView = (product) => { quickViewProduct.value = product; showQuickView.value = true }
const closeQuickView = () => { showQuickView.value = false; quickViewProduct.value = null }
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>


