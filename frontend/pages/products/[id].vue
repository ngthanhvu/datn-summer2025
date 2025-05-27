<template>
  <div class="tw-container tw-mx-auto tw-px-4 tw-py-8">
    <!-- Breadcrumb -->
    <div class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-text-gray-500 tw-mb-6">
      <NuxtLink to="/" class="hover:tw-text-blue-600">Trang chủ</NuxtLink>
      <span>/</span>
      <NuxtLink to="/products" class="hover:tw-text-blue-600">Sản phẩm</NuxtLink>
      <span>/</span>
      <span class="tw-text-gray-900">Áo thun nam basic</span>
    </div>

    <!-- Product Section -->
    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-8 tw-mb-12">
      <!-- Product Images -->
      <div class="tw-space-y-4">
        <!-- Main Image -->
        <div class="tw-aspect-square tw-rounded-lg tw-overflow-hidden tw-bg-gray-100 tw-cursor-zoom-in"
          @click="showZoomModal = true">
          <img :src="mainImage" alt="Product" class="tw-w-full tw-h-full tw-object-cover" />
        </div>
        <!-- Thumbnails -->
        <div class="tw-grid tw-grid-cols-4 tw-gap-4">
          <button v-for="(image, index) in productImages" :key="index" @click="mainImage = image"
            class="tw-aspect-square tw-rounded-lg tw-overflow-hidden tw-bg-gray-100 hover:tw-ring-2 hover:tw-ring-blue-500">
            <img :src="image" alt="Thumbnail" class="tw-w-full tw-h-full tw-object-cover" />
          </button>
        </div>
      </div>

      <!-- Zoom Modal -->
      <div v-if="showZoomModal"
        class="tw-fixed tw-inset-0 tw-bg-black/80 tw-z-50 tw-flex tw-items-center tw-justify-center"
        @click="showZoomModal = false">
        <div class="tw-relative tw-w-[90vw] tw-h-[90vh] tw-max-w-6xl" @click.stop>
          <img :src="mainImage" alt="Product" class="tw-w-full tw-h-full tw-object-contain" />
          <button @click="showZoomModal = false" class="tw-absolute tw-top-4 tw-right-4">
            <i class="bi bi-x-lg tw-text-xl tw-text-white"></i>
          </button>
        </div>
      </div>

      <!-- Product Info -->
      <div class="tw-space-y-6">
        <div>
          <h1 class="tw-text-2xl tw-font-bold tw-mb-2">Áo thun nam basic</h1>
          <p class="tw-text-gray-500">Danh mục: <NuxtLink to="/category/ao-thun"
              class="tw-text-blue-600 hover:tw-underline">Áo thun</NuxtLink>
          </p>
        </div>

        <!-- Price -->
        <div class="tw-space-y-2">
          <div class="tw-flex tw-items-center tw-gap-4">
            <span class="tw-text-2xl tw-font-bold tw-text-blue-600">299,000₫</span>
            <span class="tw-text-lg tw-text-gray-400 tw-line-through">399,000₫</span>
            <span class="tw-bg-red-500 tw-text-white tw-px-2 tw-py-1 tw-rounded-full tw-text-sm">-25%</span>
          </div>
          <p class="tw-text-sm tw-text-gray-500">Giá đã bao gồm VAT</p>
        </div>

        <!-- Variants -->
        <div class="tw-space-y-4">
          <!-- Size -->
          <div>
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
          <div>
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
              <input type="number" v-model="quantity" min="1" class="tw-w-16 tw-text-center tw-border-x tw-py-2" />
              <button @click="quantity++" class="tw-px-3 tw-py-2 hover:tw-bg-gray-100">+</button>
            </div>
            <span class="tw-text-sm tw-text-gray-500">Còn lại: 50 sản phẩm</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="tw-flex tw-gap-4">
          <button
            class="tw-flex-1 tw-bg-blue-600 tw-text-white tw-py-3 tw-rounded-md hover:tw-bg-blue-700 tw-transition-colors">
            Thêm vào giỏ hàng
          </button>
          <button class="tw-p-3 tw-border tw-border-gray-300 tw-rounded-md hover:tw-bg-gray-100 tw-transition-colors">
            <i class="bi bi-heart tw-text-xl"></i>
          </button>
        </div>

        <!-- Status -->
        <div class="tw-flex tw-items-center tw-gap-2 tw-text-sm">
          <span class="tw-text-green-600 tw-font-medium">Còn hàng</span>
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
        <p class="tw-text-gray-600">
          Áo thun nam basic với chất liệu cotton 100% mềm mại, thoáng mát. Thiết kế đơn giản, dễ phối đồ.
          Phù hợp cho mọi dịp từ đi làm đến dạo phố.
        </p>
        <ul class="tw-list-disc tw-list-inside tw-mt-4 tw-space-y-2">
          <li>Chất liệu: Cotton 100%</li>
          <li>Kiểu dáng: Regular fit</li>
          <li>Cổ tròn</li>
          <li>Phù hợp mặc hàng ngày</li>
        </ul>
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
          <swiper-slide v-for="i in 5" :key="i">
            <Card />
          </swiper-slide>
        </swiper>
      </div>
      <!-- Desktop Grid -->
      <div class="tw-hidden lg:tw-grid lg:tw-grid-cols-5 tw-gap-6">
        <Card v-for="i in 5" :key="i" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Card from '~/components/home/Card.vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'

// Zoom modal state
const showZoomModal = ref(false)

// Product images
const mainImage = ref('https://product.hstatic.net/200000696635/product/frame_25_fb1b30c611ec4ebb88fc27d011201815_d572fde53b934b5ea502c2dd0a56a0d7_large.png')
const productImages = [
  'https://product.hstatic.net/200000696635/product/frame_25_fb1b30c611ec4ebb88fc27d011201815_d572fde53b934b5ea502c2dd0a56a0d7_large.png',
  'https://product.hstatic.net/200000696635/product/frame_25_fb1b30c611ec4ebb88fc27d011201815_d572fde53b934b5ea502c2dd0a56a0d7_large.png',
  'https://product.hstatic.net/200000696635/product/frame_25_fb1b30c611ec4ebb88fc27d011201815_d572fde53b934b5ea502c2dd0a56a0d7_large.png',
  'https://product.hstatic.net/200000696635/product/frame_25_fb1b30c611ec4ebb88fc27d011201815_d572fde53b934b5ea502c2dd0a56a0d7_large.png',
]

// Product variants
const sizes = ['S', 'M', 'L', 'XL', 'XXL']
const selectedSize = ref('M')

const colors = [
  { name: 'Đen', code: '#000000' },
  { name: 'Trắng', code: '#FFFFFF' },
  { name: 'Xám', code: '#808080' },
  { name: 'Xanh navy', code: '#000080' },
]
const selectedColor = ref(colors[0])

// Quantity
const quantity = ref(1)

// Tabs
const tabs = [
  { id: 'description', name: 'Mô tả' },
  { id: 'reviews', name: 'Đánh giá' },
]
const activeTab = ref('description')

// Reviews
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

useHead({
  title: 'Áo thun nam basic - DEVGANG',
  meta: [
    { name: 'description', content: 'Áo thun nam basic với chất liệu cotton 100% mềm mại, thoáng mát. Thiết kế đơn giản, dễ phối đồ.' },
  ],
})
</script>

<style scoped>
/* Add any custom styles here */
.tw-cursor-zoom-in {
  cursor: zoom-in;
}
</style>