<template>
  <div class="container mx-auto py-8 px-4">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-2xl font-semibold">Sản phẩm yêu thích</h1>
      <p class="text-gray-600">Các sản phẩm bạn đã đánh dấu yêu thích</p>
    </div>

    <!-- Skeleton Loading -->
    <div v-if="loading"
      class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-6 max-w-[1440px] mx-auto animate-pulse">
      <div v-for="i in 5" :key="i" class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="h-80 bg-gray-200"></div>
        <div class="p-4 space-y-2">
          <div class="h-4 bg-gray-200 rounded w-3/4"></div>
          <div class="h-4 bg-gray-200 rounded w-1/2"></div>
          <div class="h-6 bg-gray-300 rounded w-20 mt-3"></div>
        </div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="text-center py-16">
      <i class="bi bi-exclamation-circle text-4xl text-red-500 mb-4"></i>
      <h2 class="text-xl font-medium mb-2">Có lỗi xảy ra</h2>
      <p class="text-gray-600 mb-6">{{ error }}</p>
      <button @click="fetchFavorites"
        class="bg-[#81AACC] text-white px-6 py-3 rounded-md hover:bg-[#6B8FA8] transition-colors">
        Thử lại
      </button>
    </div>

    <!-- Favorite Products -->
    <div v-else-if="favoriteItems.length > 0"
      class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-6 max-w-[1440px] mx-auto">
      <WishlistCard v-for="(item, index) in favoriteItems" :key="index" :product="item" @add-to-cart="addToCart"
        @quick-view="showQuickView" @remove="removeFromFavorites" />
    </div>

    <!-- Empty -->
    <div v-else class="text-center py-16">
      <i class="bi bi-heart text-4xl text-gray-400 mb-4"></i>
      <h2 class="text-xl font-medium mb-2">Chưa có sản phẩm yêu thích</h2>
      <p class="text-gray-600 mb-6">
        Bạn chưa có sản phẩm nào trong danh sách yêu thích
      </p>
      <router-link to="/san-pham"
        class="bg-[#81AACC] text-white px-6 py-3 rounded-md hover:bg-[#6B8FA8] transition-colors cursor-pointer">
        Khám phá sản phẩm
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import WishlistCard from './WishlistCard.vue'
import { useProducts } from '../../composable/useProducts'
import { useRouter } from 'vue-router'
import { push } from 'notivue'

const router = useRouter()
const { getFavoriteProducts } = useProducts()
const favoriteItems = ref([])
const loading = ref(false)
const error = ref(null)

const fetchFavorites = async () => {
  loading.value = true
  error.value = null
  try {
    const favorites = await getFavoriteProducts()
    favoriteItems.value = favorites.map((item) => ({
      image: item.product.images?.[0]?.image_path || 'https://product.hstatic.net/200000696635/product/frame_25_fb1b30c611ec4ebb88fc27d011201815_d572fde53b934b5ea502c2dd0a56a0d7_large.png',
      name: item.product.name,
      category: item.product.category?.name || 'Khác',
      price: item.product.price,
      originalPrice: item.product.original_price,
      colors: item.product.colors || [],
      slug: item.product.slug,
    }))
  } catch (err) {
    error.value = 'Không thể tải sản phẩm yêu thích. Vui lòng thử lại sau.'
    console.error('Error fetching favorite products:', err)
  } finally {
    loading.value = false
  }
}

const addToCart = (product) => {
  // Implement add to cart functionality
  console.log('Add to cart:', product)
}

const showQuickView = (product) => {
  router.push(`/products/${product.slug}`)
}

const removeFromFavorites = async (product) => {
  try {
    const { toggleFavorite } = useProducts()
    await toggleFavorite(product.slug)
    await fetchFavorites()
    push.success('Đã xóa khỏi danh sách yêu thích!')
  } catch (err) {
    console.error('Error removing from favorites:', err)
    error.value = 'Không thể xóa sản phẩm khỏi yêu thích. Vui lòng thử lại.'
  }
}

onMounted(fetchFavorites)
</script>

<style scoped>
:deep(.card-image) {
  height: 400px;
  object-fit: cover;
}
</style>