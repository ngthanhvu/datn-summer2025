<template>
  <NuxtLink :to="`/chi-tiet/${product.slug}`" class="tw-block">
    <div class="tw-w-full tw-overflow-hidden tw-group tw-pb-2 sm:tw-pb-3 tw-relative tw-rounded-[5px] tw-bg-white">
      <!-- Image wrapper -->
      <div class="tw-relative tw-overflow-hidden">
        <img :src="getMainImage" :alt="product.name"
          class="tw-w-full tw-object-cover tw-h-48 sm:tw-h-80 tw-transition-transform group-hover:tw-scale-105 tw-duration-300" />

        <!-- Hover overlay -->
        <div
          class="tw-absolute tw-inset-0 tw-bg-black/10 tw-opacity-0 group-hover:tw-opacity-100 tw-transition-opacity tw-duration-300">
        </div>

        <!-- Hover buttons -->
        <div
          class="tw-absolute tw-bottom-2 tw-left-1/2 -tw-translate-x-1/2 tw-flex tw-gap-1 sm:tw-gap-2 tw-opacity-0 tw-translate-y-4 group-hover:tw-opacity-100 group-hover:tw-translate-y-0 tw-transition-all tw-duration-300">
          <!-- Add to cart -->
          <button
            class="tw-bg-white tw-rounded tw-w-8 tw-h-8 sm:tw-w-10 sm:tw-h-10 tw-flex tw-items-center tw-justify-center tw-shadow hover:tw-bg-gray-100 tw-transition tw-duration-200"
            title="Thêm vào giỏ" @click.prevent="addToCart">
            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-4 tw-w-4 sm:tw-h-5 sm:tw-w-5" fill="none"
              viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.35 2.7a1 1 0 00.9 1.5H19M7 13L5.4 5M16 16a1 1 0 100 2 1 1 0 000-2zm-8 0a1 1 0 100 2 1 1 0 000-2z" />
            </svg>
          </button>

          <!-- Quick view -->
          <button
            class="tw-bg-white tw-rounded tw-w-8 tw-h-8 sm:tw-w-10 sm:tw-h-10 tw-flex tw-items-center tw-justify-center tw-shadow hover:tw-bg-gray-100 tw-transition tw-duration-200"
            title="Xem nhanh" @click.prevent="onQuickView">
            <svg xmlns="http://www.w3.org/2000/svg" class="tw-h-4 tw-w-4 sm:tw-h-5 sm:tw-w-5" fill="none"
              viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Product info -->
      <div class="tw-px-2 sm:tw-px-3 tw-mt-1 sm:tw-mt-2">
        <p class="tw-text-xs tw-uppercase tw-text-gray-400 tw-truncate">{{ categoryName }}</p>
        <p class="tw-text-sm sm:tw-text-sm tw-font-medium tw-text-gray-900 tw-line-clamp-2 tw-leading-tight">{{
          product.name }}</p>

        <!-- Price -->
        <div class="tw-flex tw-items-center tw-gap-1 sm:tw-gap-2 tw-mt-1 tw-flex-wrap">
          <p class="tw-text-[#81AACC] tw-font-semibold tw-text-sm sm:tw-text-base">{{ formatPrice(product.discount_price
            ||
            product.price) }}</p>
          <template v-if="product.discount_price && product.discount_price < product.price">
            <p class="tw-text-gray-400 tw-line-through tw-text-xs sm:tw-text-sm">{{ formatPrice(product.price) }}</p>
            <span class="tw-bg-red-600 tw-text-white tw-text-xs tw-rounded-full tw-px-1 sm:tw-px-2 tw-py-[1px]">
              -{{ calculateDiscount(product.price, product.discount_price) }}%
            </span>
          </template>
        </div>

        <!-- Variants & extra -->
        <div class="tw-flex tw-items-center tw-justify-between tw-mt-2 sm:tw-mt-3">
          <div class="tw-flex tw-items-center tw-gap-1" v-if="product.variants && product.variants.length">
            <div v-for="(variant, index) in displayedVariants" :key="index"
              class="tw-w-3 tw-h-3 sm:tw-w-4 sm:tw-h-4 tw-rounded-full tw-border tw-border-gray-300"
              :style="{ backgroundColor: variant.color }">
            </div>
            <span v-if="uniqueVariantCount > maxDisplayVariants" class="tw-text-xs tw-text-gray-500">
              +{{ uniqueVariantCount - maxDisplayVariants }}
            </span>
          </div>

          <!-- Like Icon -->
          <FavoriteButton :product-slug="product.slug" @click.prevent />
        </div>
      </div>
    </div>
  </NuxtLink>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import FavoriteButton from '../common/FavoriteButton.vue'
import { useCategoryStore } from '~/stores/useCategoryStore.js'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['quick-view'])

const categoryStore = useCategoryStore()
const categoryName = ref('Khác')
const maxDisplayVariants = 3

onMounted(async () => {
  if (props.product.categories_id) {
    try {
      const category = await categoryStore.fetchCategoryById(props.product.categories_id)
      categoryName.value = category.name
    } catch (error) {
      console.error('Error fetching category:', error)
    }
  }
})

// Lọc ra các màu duy nhất từ variants
const displayedVariants = computed(() => {
  if (!props.product.variants) return []
  const uniqueColors = []
  const seen = new Set()
  for (const variant of props.product.variants) {
    if (variant.color && !seen.has(variant.color)) {
      seen.add(variant.color)
      uniqueColors.push(variant)
    }
  }
  return uniqueColors.slice(0, maxDisplayVariants)
})

const uniqueVariantCount = computed(() => {
  if (!props.product.variants) return 0
  return new Set(props.product.variants.map(v => v.color)).size
})

const getMainImage = computed(() => {
  const mainImage = props.product.images?.find(img => img.is_main === 1)
  return mainImage ? mainImage.image_path : props.product.images?.[0]?.image_path
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price)
}

const calculateDiscount = (originalPrice, discountPrice) => {
  return Math.round(((originalPrice - discountPrice) / originalPrice) * 100)
}

const addToCart = () => {
  // TODO: Implement add to cart functionality
  console.log('Add to cart:', props.product)
}

function onQuickView() {
  emit('quick-view', props.product)
}
</script>

<style scoped>
/* Custom line clamp for better text truncation */
.tw-line-clamp-2 {
  display: -webkit-box;
  line-clamp: 2;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Ensure proper spacing on very small screens */
@media (max-width: 480px) {
  .tw-line-clamp-2 {
    line-height: 1.2;
  }
}
</style>