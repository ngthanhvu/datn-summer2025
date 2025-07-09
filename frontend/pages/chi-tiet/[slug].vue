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
        <div class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-text-gray-500 tw-mb-6 tw-max-w-7xl tw-mx-auto">
          <NuxtLink to="/" class="hover:tw-text-[#81AACC]">Trang chủ</NuxtLink>
          <span>/</span>
          <NuxtLink to="/san-pham" class="hover:tw-text-[#81AACC]">Sản phẩm</NuxtLink>
          <span>/</span>
          <span class="tw-text-gray-900">{{ data.name }}</span>
          <span v-if="flashSaleName" class="tw-block tw-text-base tw-text-red-500 tw-font-semibold tw-ml-2">
            {{ flashSaleName }}
          </span>
          
        </div>

        <!-- Product Detail Component -->
        <Detail :product="data" :product-images="productImages" v-model:main-image="mainImage"
          :selected-size="selectedSize" :selected-color="selectedColor" :quantity="quantity"
          :selected-variant-stock="selectedVariantStock" :display-price="displayPrice"
          :show-original-price="showOriginalPrice" :active-tab="activeTab" :review-stats="reviewStats"
          :show-review-form="showReviewForm" :is-authenticated="isAuthenticated" :user-has-reviewed="userHasReviewed"
          :user-review="userReview" :review-form="reviewForm" :editing-review-id="editingReviewId"
          :is-submitting="isSubmitting" :preview-images="previewImages" :reviews-loading="reviewsLoading"
          :reviews="reviews" :review-pagination-data="reviewPaginationData" :total-review-pages="totalReviewPages"
          :total-reviews="totalReviews" :reviews-per-page="reviewsPerPage" :current-review-page="currentReviewPage"
          :user="user" :related-products="relatedProducts"
          :flash-sale-name="flashSaleName" :flash-sale-price="flashSalePrice" :flash-sale-end-time="flashSaleEndTime" :flash-sale-sold="flashSaleSold" :flash-sale-quantity="flashSaleQuantity" :product-raw="data"
          :flash-sale-percent="flashSalePercent"
          @update:selected-size="selectedSize = $event"
          @update:selected-color="selectedColor = $event" @update:quantity="quantity = $event"
          @update:active-tab="activeTab = $event" @add-to-cart="addToCart" @update:review-form="reviewForm = $event"
          @update:show-review-form="showReviewForm = $event" @submit-review="submitReview"
          @handle-image-upload="handleImageUpload" @remove-image="removeImage" @cancel-edit="cancelEdit"
          @edit-review="editReview" @remove-review="removeReview" @open-image-modal="openImageModal"
          @handle-review-page-change="handleReviewPageChange" />
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
import { ref, computed, watch, onMounted } from 'vue'
import Detail from '~/components/product-detail/Detail.vue'
import useCarts from '~/composables/useCarts'
import { useCookie } from '#app'
import { useReviews } from '~/composables/useReviews'
import { useAuth } from '~/composables/useAuth'
const notyf = useNuxtApp().$notyf

const runtimeConfig = useRuntimeConfig()
const route = useRoute()
const { getProducts, getProductBySlug } = useProducts()
const { getInventories } = useInventories()
const { addToCart: addToCartComposable, getUserId, transferCartFromSessionToUser, fetchCart } = useCarts()
const { getReviewsByProductSlug, addReview, updateReview, deleteReview, checkUserReview } = useReviews()
const { user, isAuthenticated } = useAuth()

const userHasReviewed = ref(false)
const userReview = ref(null)

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
    watch: [() => route.params.slug],
    server: false,
    lazy: true
  }
)

const showZoomModal = ref(false)

const selectedSize = ref('')
const selectedColor = ref(null)
const hoveredSize = ref('')
const hoveredColor = ref(null)

const firstVariantOfColor = computed(() => {
  if (!data.value?.variants?.length || !selectedColor.value) return null
  return data.value.variants.find(v => v.color === selectedColor.value.name)
})

const mainImage = ref('')
const colorVariantImages = computed(() => {
  return firstVariantOfColor.value?.images?.length
    ? firstVariantOfColor.value.images.map(img => img.image_path)
    : []
})

const isManualImageSelection = ref(false)

watch([colorVariantImages, data], () => {
  if (!isManualImageSelection.value) {
    if (data.value?.images?.length) {
      const mainImg = data.value.images.find(img => img.is_main) || data.value.images[0]
      mainImage.value = mainImg.image_path
    } else if (colorVariantImages.value.length) {
      mainImage.value = colorVariantImages.value[0]
    } else {
      mainImage.value = '/images/placeholder.jpg'
    }
  }
}, { immediate: true })

const activeVariant = computed(() => {
  if (!data.value?.variants?.length) return null
  return data.value.variants.find(
    v => v.size === selectedSize.value && v.color === selectedColor.value?.name
  )
})

const productImages = computed(() => {
  const mainImages = data.value?.images?.length
    ? data.value.images.map(img => img.image_path)
    : ['/images/placeholder.jpg']

  if (selectedColor.value && colorVariantImages.value.length > 0) {
    const variantImages = colorVariantImages.value.filter(img => !mainImages.includes(img))
    return [...mainImages, ...variantImages]
  }

  return mainImages
})

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

const reviews = ref([])
const reviewStats = ref({
  average: 0,
  total: 0,
  distribution: [
    { stars: 5, percentage: 0 },
    { stars: 4, percentage: 0 },
    { stars: 3, percentage: 0 },
    { stars: 2, percentage: 0 },
    { stars: 1, percentage: 0 }
  ]
})

const currentReviewPage = ref(1)
const reviewsPerPage = ref(3)
const totalReviewPages = ref(1)
const totalReviews = ref(0)
const reviewPaginationData = ref(null)
const reviewsLoading = ref(false)

const fetchReviews = async (page = 1) => {
  try {
    reviewsLoading.value = true
    const userId = isAuthenticated.value && user.value ? user.value.id : null
    const response = await getReviewsByProductSlug(data.value.slug, page, reviewsPerPage.value, userId)

    reviewPaginationData.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      per_page: response.per_page,
      total: response.total,
      from: response.from,
      to: response.to
    }

    currentReviewPage.value = response.current_page
    totalReviewPages.value = response.last_page
    totalReviews.value = response.total

    // Hiển thị tất cả đánh giá đã duyệt và không bị ẩn, cộng với đánh giá của chính user (nếu có)
    reviews.value = response.data.filter(review => {
      if (userId && review.user_id === userId) return true;
      return !!review.is_approved && !review.is_hidden;
    })

    if (reviews.value.length > 0) {
      const total = response.total
      const sum = reviews.value.reduce((acc, review) => acc + review.rating, 0)
      const average = sum / total

      const distribution = [5, 4, 3, 2, 1].map(stars => {
        const count = reviews.value.filter(r => r.rating === stars).length
        return {
          stars,
          percentage: Math.round((count / total) * 100)
        }
      })

      reviewStats.value = {
        average: parseFloat(average.toFixed(1)),
        total,
        distribution
      }
    }

    if (isAuthenticated.value && user.value) {
      await checkUserHasReviewed()
    }
  } catch (error) {
    console.error('Error fetching reviews:', error)
  } finally {
    reviewsLoading.value = false
  }
}

onMounted(() => {
  if (data.value) {
    fetchReviews()
  }
})

watch(data, async () => {
  if (data.value) {
    await Promise.all([
      fetchReviews(),
      fetchRelatedProducts()
    ])
  }
}, { immediate: true })

const relatedProducts = ref([])

const fetchRelatedProducts = async () => {
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
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price)
}

const addToCart = async () => {
  try {
    const selectedVariant = data.value.variants.find(v =>
      v.size === selectedSize.value &&
      v.color === selectedColor.value?.name
    )
    if (!selectedVariant) {
      notyf.error('Vui lòng chọn size và màu sắc')
      return
    }
    if (quantity.value > selectedVariant.stock) {
      notyf.error('Số lượng vượt quá số lượng trong kho')
      return
    }
    // Tính đúng giá hiển thị của biến thể đang chọn (giống UI)
    let price = selectedVariant.price
    if (flashSalePrice.value && data.value.price) {
      const percent = Math.round(100 - (flashSalePrice.value / data.value.price) * 100)
      if (percent > 0) {
        price = Math.round(selectedVariant.price * (1 - percent / 100))
      }
    }
    await addToCartComposable(selectedVariant.id, quantity.value, price)
    notyf.success('Đã thêm vào giỏ hàng')
  } catch (error) {
    console.error('Error adding to cart:', error)
    notyf.error('Có lỗi xảy ra khi thêm vào giỏ hàng')
  }
}

const mergeCartAfterLogin = async () => {
  await transferCartFromSessionToUser()
  await fetchCart()
}

useHead(() => ({
  title: data.value ? `${data.value.name} - DEVGANG` : 'Đang tải sản phẩm...',
  meta: [
    {
      name: 'description',
      content: data.value?.description || data.value?.name || 'Sản phẩm nổi bật từ DEVGANG'
    },
    { property: 'og:title', content: data.value?.name || '' },
    { property: 'og:description', content: data.value?.description || '' },
    { property: 'og:image', content: data.value?.images?.[0]?.image_path || '/default-og.jpg' },
    { property: 'og:type', content: 'product' },
    { property: 'og:url', content: typeof window !== 'undefined' ? window.location.href : '' }
  ]
}))

const reviewForm = ref({
  rating: 5,
  content: '',
  images: []
})

const editingReviewId = ref(null)
const isSubmitting = ref(false)
const previewImages = ref([])
const deleteImageIds = ref([])

const showReviewForm = ref(false)

const handleImageUpload = (event) => {
  const files = event.target.files
  if (!files.length) return

  for (let i = 0; i < files.length; i++) {
    const file = files[i]
    reviewForm.value.images.push(file)

    const reader = new FileReader()
    reader.onload = (e) => {
      previewImages.value.push({
        url: e.target.result,
        file: file
      })
    }
    reader.readAsDataURL(file)
  }
}

const removeImage = (index) => {
  if (previewImages.value[index].existing && previewImages.value[index].id) {
    deleteImageIds.value.push(previewImages.value[index].id)
  }

  previewImages.value.splice(index, 1)
  reviewForm.value.images.splice(index, 1)
}

const submitReview = async () => {
  if (!isAuthenticated.value) {
    notyf.info('Vui lòng đăng nhập để đánh giá sản phẩm')
    return
  }

  if (!reviewForm.value.content.trim()) {
    notyf.error('Vui lòng nhập nội dung đánh giá')
    return
  }

  try {
    isSubmitting.value = true

    const reviewData = {
      user_id: user.value.id,
      product_slug: data.value.slug,
      rating: reviewForm.value.rating,
      content: reviewForm.value.content,
      images: reviewForm.value.images
    }

    if (editingReviewId.value && deleteImageIds.value.length > 0) {
      reviewData.delete_image_ids = deleteImageIds.value
    }

    if (editingReviewId.value) {
      await updateReview(editingReviewId.value, reviewData)
      notyf.success('Đã cập nhật đánh giá thành công')
    } else {
      await addReview(reviewData)
      notyf.success('Đã gửi đánh giá thành công. Đánh giá sẽ được hiển thị sau khi được duyệt.')
    }

    reviewForm.value = {
      rating: 5,
      content: '',
      images: []
    }
    previewImages.value = []
    deleteImageIds.value = []
    editingReviewId.value = null
    showReviewForm.value = false

    await fetchReviews(1)
  } catch (error) {
    console.error('Lỗi khi gửi đánh giá:', error)

    if (error.response && error.response.status === 422) {
      notyf.error('Bạn đã đánh giá sản phẩm này rồi. Vui lòng chỉnh sửa đánh giá hiện có thay vì tạo mới.')
      await checkUserHasReviewed()
    } else {
      notyf.error('Có lỗi xảy ra khi gửi đánh giá')
    }
  } finally {
    isSubmitting.value = false
  }
}

const editReview = (review) => {
  editingReviewId.value = review.id
  reviewForm.value.rating = review.rating
  reviewForm.value.content = review.content
  reviewForm.value.images = []

  previewImages.value = []
  if (review.images && review.images.length > 0) {
    review.images.forEach(image => {
      previewImages.value.push({
        url: runtimeConfig.public.apiBaseUrl + '/storage/' + image.image_path,
        id: image.id,
        existing: true
      })
    })
  }

  showReviewForm.value = true
  document.getElementById('review-form').scrollIntoView({ behavior: 'smooth' })
}

const cancelEdit = () => {
  editingReviewId.value = null
  reviewForm.value = {
    rating: 5,
    content: '',
    images: []
  }
  previewImages.value = []
  deleteImageIds.value = []
  showReviewForm.value = false
}

const removeReview = async (reviewId) => {
  if (!confirm('Bạn có chắc chắn muốn xóa đánh giá này?')) return

  try {
    await deleteReview(reviewId)
    notyf.success('Đã xóa đánh giá thành công')

    const currentPageReviews = reviews.value.length
    if (currentPageReviews === 1 && currentReviewPage.value > 1) {
      await fetchReviews(currentReviewPage.value - 1)
    } else {
      await fetchReviews(currentReviewPage.value)
    }
  } catch (error) {
    console.error('Lỗi khi xóa Đánh giá:', error)
    notyf.error('Có lỗi xảy ra khi xóa đánh giá')
  }
}

const canModifyReview = (review) => {
  return isAuthenticated.value && user.value && review.user_id === user.value.id
}

const checkUserHasReviewed = async () => {
  if (!isAuthenticated.value || !user.value || !data.value) return

  try {
    const response = await checkUserReview(user.value.id, data.value.slug)
    userHasReviewed.value = response.hasReviewed
    userReview.value = response.review || null

    // Kiểm tra trạng thái đánh giá của user
    if (response.hasReviewed && response.review) {
      const review = response.review
      if (review.is_hidden) {
        notyf.info('Đánh giá của bạn chứa từ khóa không phù hợp và đã bị ẩn')
      } else if (!review.is_approved) {
        notyf.info('Đánh giá của bạn đang chờ duyệt')
      }
    }

  } catch (error) {
    console.error('Lỗi khi kiểm tra đánh giá của người dùng:', error)
  }
}

const handleReviewPageChange = (page) => {
  currentReviewPage.value = page
  fetchReviews(page)
}

const getVisibleReviewPages = () => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentReviewPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalReviewPages.value, start + maxVisible - 1)

  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
}

const flashSaleName = computed(() => route.query.flashsale)
const flashSalePrice = computed(() => {
  const price = route.query.flash_price
  return price ? Number(price) : null
})
const flashSaleEndTime = computed(() => route.query.end_time)
const flashSaleSold = computed(() => Number(route.query.sold) || 0)
const flashSaleQuantity = computed(() => Number(route.query.quantity) || 0)

const flashSalePercent = computed(() => {
  if (!flashSalePrice.value || !data.value?.price) return 0
  return Math.round(100 - (flashSalePrice.value / data.value.price) * 100)
})

const displayPrice = computed(() => {
  if (flashSalePrice.value) return flashSalePrice.value
  if (activeVariant.value && activeVariant.value.price) {
    return activeVariant.value.price
  }
  return data.value?.discount_price && data.value.discount_price < data.value.price
    ? data.value.discount_price
    : data.value?.price
})

const showOriginalPrice = computed(() => {
  if (flashSalePrice.value) return true
  return data.value && displayPrice.value < data.value.price
})

const openImageModal = (imageUrl) => {
  mainImage.value = imageUrl
  showZoomModal.value = true
}

// Thêm function để xử lý thay đổi ảnh thủ công
const updateMainImage = (newImage) => {
  console.log('updateMainImage called with:', newImage) // Debug log
  isManualImageSelection.value = true
  mainImage.value = newImage
}

// Reset manual selection khi màu sắc thay đổi
watch(selectedColor, () => {
  isManualImageSelection.value = false
  // Đảm bảo ảnh được cập nhật ngay lập tức khi màu thay đổi
  if (colorVariantImages.value.length) {
    mainImage.value = colorVariantImages.value[0]
  }
})

// Track manual image changes
watch(mainImage, (newImage, oldImage) => {
  // Nếu ảnh thay đổi không phải do watch tự động, đánh dấu là lựa chọn thủ công
  if (oldImage && newImage !== oldImage && !isManualImageSelection.value) {
    // Kiểm tra xem ảnh mới có phải là ảnh đầu tiên của variant không
    const isFirstVariantImage = colorVariantImages.value.length > 0 && newImage === colorVariantImages.value[0]
    const isFirstMainImage = data.value?.images?.length > 0 && newImage === data.value.images[0]?.image_path

    if (!isFirstVariantImage && !isFirstMainImage) {
      isManualImageSelection.value = true
    }
  }
})

function getDiscountPercent(price, flashPrice) {
  if (!price || !flashPrice) return 0
  return Math.round(100 - (flashPrice / price) * 100)
}

function getSoldPercent(product) {
  if (product.quantity && product.sold) {
    let percent = Math.round((product.sold / (product.quantity + product.sold)) * 100)
    return Math.max(percent, 10)
  }
  return 50
}
</script>

<style scoped>
.tw-cursor-zoom-in {
  cursor: zoom-in;
}
</style>
