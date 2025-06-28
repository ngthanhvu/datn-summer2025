<template>
  <div class="tw-container tw-mx-auto tw-py-8 tw-px-4">
    <!-- Header for Favorite Products Section -->
    <div class="tw-mb-8">
      <h1 class="tw-text-2xl tw-font-semibold">Sản phẩm yêu thích</h1>
      <p class="tw-text-gray-600">Các sản phẩm bạn đã đánh dấu yêu thích</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="tw-text-center tw-py-16">
      <div
        class="tw-animate-spin tw-inline-block tw-w-8 tw-h-8 tw-border-4 tw-border-current tw-border-t-transparent tw-text-blue-600 tw-rounded-full">
      </div>
      <p class="tw-mt-4 tw-text-gray-600">Đang tải sản phẩm yêu thích...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="tw-text-center tw-py-16">
      <i class="bi bi-exclamation-circle tw-text-4xl tw-text-red-500 tw-mb-4"></i>
      <h2 class="tw-text-xl tw-font-medium tw-mb-2">Có lỗi xảy ra</h2>
      <p class="tw-text-gray-600 tw-mb-6">{{ error }}</p>
      <button @click="fetchFavorites"
        class="tw-bg-[#81AACC] tw-text-white tw-px-6 tw-py-3 tw-rounded-md hover:tw-bg-[#6B8FA8] tw-transition-colors">
        Thử lại
      </button>
    </div>

    <!-- Favorite Products Grid -->
    <div v-else-if="favoriteItems.length > 0"
      class="tw-grid tw-grid-cols-2 md:tw-grid-cols-3 lg:tw-grid-cols-4 xl:tw-grid-cols-5 tw-gap-3 md:tw-gap-6 tw-max-w-[1440px] tw-mx-auto">
      <WishlistCard v-for="(item, index) in favoriteItems" :key="index" :product="item" @add-to-cart="addToCart"
        @quick-view="showQuickView" @remove="removeFromFavorites" />
    </div>

    <!-- Empty State -->
    <div v-else class="tw-text-center tw-py-16">
      <i class="bi bi-heart tw-text-4xl tw-text-gray-400 tw-mb-4"></i>
      <h2 class="tw-text-xl tw-font-medium tw-mb-2">Chưa có sản phẩm yêu thích</h2>
      <p class="tw-text-gray-600 tw-mb-6">
        Bạn chưa có sản phẩm nào trong danh sách yêu thích
      </p>
      <NuxtLink to="/product"
        class="tw-bg-[#81AACC] tw-text-white tw-px-6 tw-py-3 tw-rounded-md hover:tw-bg-[#6B8FA8] tw-transition-colors">
        Khám phá sản phẩm
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import WishlistCard from '~/components/wishlist/WishlistCard.vue'
import { useProducts } from '~/composables/useProducts'
import { useRouter } from 'vue-router'
const notyf = useNuxtApp().$notyf

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
    notyf.success('Đã xóa khỏi danh sách yêu thích!')
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