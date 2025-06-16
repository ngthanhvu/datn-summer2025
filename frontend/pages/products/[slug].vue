<template>
  <ClientOnly>
    <div class="tw-container tw-mx-auto tw-px-4 tw-py-8">
      <!-- Loading state -->
      <div v-if="pending" class="tw-flex tw-justify-center tw-items-center tw-min-h-[400px]">
        <div class="tw-text-xl">Đang tải...</div>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="tw-flex tw-justify-center tw-items-center tw-min-h-[400px]">
        <div class="tw-text-red-500">{{ error }}</div>
      </div>

      <!-- Product content -->
      <div v-else-if="data">
        <!-- Breadcrumb -->
        <div class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-text-gray-500 tw-mb-6">
          <NuxtLink to="/" class="hover:tw-text-blue-600">Trang chủ</NuxtLink>
          <span>/</span>
          <NuxtLink to="/products" class="hover:tw-text-blue-600">Sản phẩm</NuxtLink>
          <span>/</span>
          <span class="tw-text-gray-900">{{ data.name }}</span>
        </div>

        <!-- Product Section -->
        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-12">
          <!-- Product Images -->
          <div class="tw-space-y-4">
            <!-- Main Image -->
            <div class="tw-aspect-square tw-rounded-lg tw-overflow-hidden tw-bg-gray-100 tw-cursor-zoom-in"
              @click="showZoomModal = true">
              <img :src="mainImage" :alt="data.name" class="tw-w-full tw-h-full tw-object-cover" />
            </div>
            <!-- Thumbnails -->
            <div class="tw-grid tw-grid-cols-4 tw-gap-4">
              <button v-for="(image, index) in productImages" :key="index" @click="mainImage = image"
                class="tw-aspect-square tw-rounded-lg tw-overflow-hidden tw-bg-gray-100 hover:tw-ring-2 hover:tw-ring-blue-500">
                <img :src="image" :alt="data.name" class="tw-w-full tw-h-full tw-object-cover" />
              </button>
            </div>
          </div>

          <!-- Product Info -->
          <div class="tw-space-y-6">
            <div>
              <h1 class="tw-text-2xl tw-font-bold tw-mb-2">{{ data.name }}</h1>
              <p class="tw-text-gray-500">Danh mục: <NuxtLink :to="'/category/' + data.category?.slug"
                  v-if="data.category" class="tw-text-blue-600 hover:tw-underline">{{ data.category.name }}</NuxtLink>
              </p>
            </div>

            <!-- Price -->
            <div class="tw-space-y-2">
              <div class="tw-flex tw-items-center tw-gap-4">
                <span class="tw-text-2xl tw-font-bold tw-text-blue-600">{{ formatPrice(data.discount_price ||
                  data.price) }}</span>
                <span v-if="data.discount_price && data.discount_price < data.price"
                  class="tw-text-lg tw-text-gray-400 tw-line-through">
                  {{ formatPrice(data.price) }}
                </span>
                <span v-if="data.discount_percentage"
                  class="tw-bg-red-500 tw-text-white tw-px-2 tw-py-1 tw-rounded-full tw-text-sm">
                  -{{ data.discount_percentage }}%
                </span>
              </div>
              <p class="tw-text-sm tw-text-gray-500">Giá đã bao gồm VAT</p>
            </div>

            <!-- Variants -->
            <div class="tw-space-y-4" v-if="data.variants && data.variants.length > 0">
              <!-- Size -->
              <div v-if="sizes.length > 0">
                <h3 class="tw-font-medium tw-mb-2">Kích thước</h3>
                <div class="tw-flex tw-gap-2">
                  <button v-for="size in sizes" :key="size" @click="selectedSize = size" :class="[
                    'tw-px-4 tw-py-2 tw-border tw-rounded-md tw-transition-colors',
                    selectedSize === size
                      ? 'tw-bg-blue-600 tw-text-white tw-border-blue-600'
                      : 'tw-border-gray-300 hover:tw-border-blue-600'
                  ]">
                    {{ size }}
                  </button>
                </div>
              </div>

              <!-- Color -->
              <div v-if="colors.length > 0">
                <h3 class="tw-font-medium tw-mb-2">Màu sắc</h3>
                <div class="tw-flex tw-gap-2">
                  <button v-for="color in colors" :key="color.name" @click="selectedColor = color" :class="[
                    'tw-w-10 tw-h-10 tw-rounded-full tw-border-2 tw-transition-colors',
                    selectedColor === color
                      ? 'tw-border-blue-600'
                      : 'tw-border-gray-300 hover:tw-border-blue-600'
                  ]" :style="{ backgroundColor: color.code }" :title="color.name">
                  </button>
                </div>
              </div>
            </div>

            <!-- Quantity -->
            <div>
              <h3 class="tw-font-medium tw-mb-2">Số lượng</h3>
              <div class="tw-flex tw-items-center tw-gap-4">
                <div class="tw-flex tw-items-center tw-border tw-rounded-md">
                  <button @click="quantity > 1 && quantity--" class="tw-px-3 tw-py-2 hover:tw-bg-gray-100">-</button>
                  <input type="number" v-model="quantity" min="1" :max="selectedVariantStock"
                    class="tw-w-16 tw-text-center tw-border-x tw-py-2" />
                  <button @click="quantity < selectedVariantStock && quantity++"
                    class="tw-px-3 tw-py-2 hover:tw-bg-gray-100">+</button>
                </div>
                <span class="tw-text-sm tw-text-gray-500">Còn lại: {{ selectedVariantStock }} sản phẩm</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="tw-flex tw-gap-4">
              <button
                class="tw-flex-1 tw-bg-blue-600 tw-text-white tw-py-3 tw-rounded-md hover:tw-bg-blue-700 tw-transition-colors"
                @click="addToCart">
                Thêm vào giỏ hàng
              </button>
            </div>

            <!-- Status -->
            <div class="tw-flex tw-items-center tw-gap-2 tw-text-sm">
              <span :class="[
                'tw-font-medium',
                selectedVariantStock > 0 ? 'tw-text-green-600' : 'tw-text-red-600'
              ]">
                {{ selectedVariantStock > 0 ? 'Còn hàng' : 'Hết hàng' }}
              </span>
              <span class="tw-text-gray-500">|</span>
              <span class="tw-text-gray-500">Giao hàng trong 1-3 ngày</span>
            </div>
          </div>
        </div>

        <!-- Description & Reviews -->
        <div class="tw-border-t tw-pt-8">
          <div class="tw-flex tw-gap-8 tw-mb-8">
            <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
              'tw-px-4 tw-py-2 tw-font-medium tw-border-b-2 tw-transition-colors',
              activeTab === tab.id
                ? 'tw-border-blue-600 tw-text-blue-600'
                : 'tw-border-transparent hover:tw-border-gray-300'
            ]">
              {{ tab.name }}
            </button>
          </div>

          <!-- Description -->
          <div v-if="activeTab === 'description'" class="tw-prose tw-max-w-none">
            <h3 class="tw-text-xl tw-font-bold tw-mb-4">Mô tả sản phẩm</h3>
            <div v-html="data.description"></div>
          </div>

          <!-- Reviews -->
          <div v-if="activeTab === 'reviews'" class="tw-space-y-6">
            <div class="tw-flex tw-items-center tw-gap-4 tw-mb-6">
              <div class="tw-text-center">
                <div class="tw-text-4xl tw-font-bold">4.5</div>
                <div class="tw-text-yellow-400 tw-flex tw-gap-1">
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-half"></i>
                </div>
                <div class="tw-text-sm tw-text-gray-500">Dựa trên 120 đánh giá</div>
              </div>
              <div class="tw-flex-1">
                <div v-for="rating in ratings" :key="rating.stars" class="tw-flex tw-items-center tw-gap-2 tw-mb-2">
                  <span class="tw-w-16">{{ rating.stars }} sao</span>
                  <div class="tw-flex-1 tw-h-2 tw-bg-gray-200 tw-rounded-full">
                    <div class="tw-h-full tw-bg-yellow-400 tw-rounded-full" :style="{ width: rating.percentage + '%' }">
                    </div>
                  </div>
                  <span class="tw-w-12 tw-text-right">{{ rating.percentage }}%</span>
                </div>
              </div>
            </div>

            <!-- Review List -->
            <div class="tw-space-y-6">
              <div v-for="review in reviews" :key="review.id" class="tw-border-b tw-pb-6">
                <div class="tw-flex tw-justify-between tw-mb-2">
                  <div class="tw-flex tw-items-center tw-gap-2">
                    <img :src="review.avatar" :alt="review.name" class="tw-w-10 tw-h-10 tw-rounded-full" />
                    <div>
                      <div class="tw-font-medium">{{ review.name }}</div>
                      <div class="tw-text-sm tw-text-gray-500">{{ review.date }}</div>
                    </div>
                  </div>
                  <div class="tw-text-yellow-400">
                    <i v-for="n in 5" :key="n" :class="n <= review.rating ? 'bi bi-star-fill' : 'bi bi-star'"></i>
                  </div>
                </div>
                <p class="tw-text-gray-600">{{ review.comment }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Related Products -->
        <div class="tw-mt-12">
          <h2 class="tw-text-2xl tw-font-bold tw-mb-6">Sản phẩm liên quan</h2>
          <!-- Mobile Slider -->
          <div class="lg:tw-hidden">
            <swiper :slides-per-view="1.2" :space-between="16" :breakpoints="{
              '640': {
                slidesPerView: 2.2,
              },
              '768': {
                slidesPerView: 3.2,
              }
            }" class="tw-w-full">
              <swiper-slide v-for="product in relatedProducts" :key="product.id">
                <Card :product="product" />
              </swiper-slide>
            </swiper>
          </div>
          <!-- Desktop Grid -->
          <div class="tw-hidden lg:tw-grid lg:tw-grid-cols-5 tw-gap-6">
            <Card v-for="product in relatedProducts" :key="product.id" :product="product" />
          </div>
        </div>
      </div>
    </div>

    <!-- Zoom Modal -->
    <div v-if="showZoomModal"
      class="tw-fixed tw-inset-0 tw-z-50 tw-bg-black/90 tw-flex tw-items-center tw-justify-center"
      @click="showZoomModal = false">
      <div class="tw-relative tw-w-full tw-h-full tw-flex tw-items-center tw-justify-center">
        <button class="tw-absolute tw-top-4 tw-right-4 tw-text-white tw-text-2xl" @click="showZoomModal = false">
          <i class="bi bi-x-lg"></i>
        </button>
        <img :src="mainImage" :alt="data?.name" class="tw-max-w-[90%] tw-max-h-[90vh] tw-object-contain" />
      </div>
    </div>
  </ClientOnly>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Card from '~/components/home/Card.vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import useCarts from '~/composables/useCarts'
import { useCookie } from '#app'
const notyf = useNuxtApp().$notyf

const route = useRoute()
const { getProducts, getProductBySlug } = useProducts()
const { getInventories } = useInventories()
const { addToCart: addToCartComposable, getUserId, transferCartFromSessionToUser, fetchCart } = useCarts()

const { data, pending, error, refresh } = await useAsyncData(
  'product',
  async () => {
    const product = await getProductBySlug(route.params.slug)

    if (product?.variants?.length) {
      const inventories = await getInventories({ product_id: product.id })

      product.variants = product.variants.map(variant => {
        const inventory = inventories.find(inv => inv.variant_id === variant.id)
        return {
          ...variant,
          stock: inventory?.quantity || 0
        }
      })
    }

    return product
  },
  {
    watch: [route.params.slug]
  }
)

const showZoomModal = ref(false)

const mainImage = ref('')
const productImages = computed(() => {
  if (!data.value?.images?.length) return ['/images/placeholder.jpg']
  return data.value.images.map(img => img.image_path)
})

watch(data, () => {
  if (data.value?.images?.length) {
    const mainImg = data.value.images.find(img => img.is_main === 1) || data.value.images[0]
    mainImage.value = mainImg.image_path
  } else {
    mainImage.value = '/images/placeholder.jpg'
  }
}, { immediate: true })

const sizes = computed(() => {
  if (!data.value?.variants?.length) return []
  const uniqueSizes = new Set()
  data.value.variants.forEach(variant => {
    if (variant.size) uniqueSizes.add(variant.size)
  })
  return Array.from(uniqueSizes)
})

const colors = computed(() => {
  if (!data.value?.variants?.length) return []
  const uniqueColors = new Set()
  data.value.variants.forEach(variant => {
    if (variant.color) uniqueColors.add(variant.color)
  })
  return Array.from(uniqueColors).map(color => ({
    name: color,
    code: color
  }))
})

const selectedSize = ref('')
const selectedColor = ref(null)

const selectedVariantStock = computed(() => {
  if (!data.value?.variants?.length) return 0

  const variant = data.value.variants.find(v =>
    v.size === selectedSize.value &&
    v.color === selectedColor.value?.name
  )

  return variant?.stock || 0
})

watch(data, () => {
  if (sizes.value.length > 0) selectedSize.value = sizes.value[0]
  if (colors.value.length > 0) selectedColor.value = colors.value[0]
}, { immediate: true })

const quantity = ref(1)

const tabs = [
  { id: 'description', name: 'Mô tả' },
  { id: 'reviews', name: 'Đánh giá' },
]
const activeTab = ref('description')

const ratings = [
  { stars: 5, percentage: 70 },
  { stars: 4, percentage: 20 },
  { stars: 3, percentage: 5 },
  { stars: 2, percentage: 3 },
  { stars: 1, percentage: 2 },
]

const reviews = [
  {
    id: 1,
    name: 'Nguyễn Văn A',
    avatar: 'https://placehold.co/40',
    date: '20/03/2024',
    rating: 5,
    comment: 'Sản phẩm chất lượng tốt, đúng như mô tả. Giao hàng nhanh chóng.',
  },
  {
    id: 2,
    name: 'Trần Thị B',
    avatar: 'https://placehold.co/40',
    date: '19/03/2024',
    rating: 4,
    comment: 'Áo đẹp, vải mềm mại. Size hơi rộng hơn một chút so với thông thường.',
  },
]

const relatedProducts = ref([])

watch(data, async () => {
  if (data.value?.categories_id) {
    try {
      const products = await getProducts()
      relatedProducts.value = products
        .filter(p => p.categories_id === data.value.categories_id && p.id !== data.value.id)
        .slice(0, 5)
    } catch (error) {
      console.error('Error fetching related products:', error)
    }
  }
}, { immediate: true })

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price)
}

const addToCart = async () => {
  try {
    // Tìm variant dựa trên size và color đã chọn
    const selectedVariant = data.value.variants.find(v =>
      v.size === selectedSize.value &&
      v.color === selectedColor.value?.name
    )
    if (!selectedVariant) {
      alert('Vui lòng chọn size và màu sắc')
      return
    }
    if (quantity.value > selectedVariant.stock) {
      alert('Số lượng vượt quá số lượng trong kho')
      return
    }
    await addToCartComposable(selectedVariant.id, quantity.value)
    notyf.success('Đã thêm vào giỏ hàng')
  } catch (error) {
    console.error('Error adding to cart:', error)
    alert('Có lỗi xảy ra khi thêm vào giỏ hàng')
  }
}

// Gọi hàm này sau khi đăng nhập thành công
const mergeCartAfterLogin = async () => {
  await transferCartFromSessionToUser()
  await fetchCart()
  alert('Đã hợp nhất giỏ hàng từ session sang tài khoản!')
}

useHead(() => ({
  title: data.value ? `${data.value.name} - DEVGANG` : 'Loading... - DEVGANG',
  meta: [
    {
      name: 'description',
      content: data.value ? data.value.description : 'Loading product details...'
    },
  ],
}))
</script>

<style scoped>
.tw-cursor-zoom-in {
  cursor: zoom-in;
}
</style>