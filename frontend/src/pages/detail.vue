<template>
    <template v-if="product">
        <ProductsDetail :product="product" :product-images="allProductImages" :main-image="mainImage"
            :selected-size="selectedSize" :selected-color="selectedColor" v-model:quantity="quantity"
            :selected-variant-stock="selectedVariantStock" :display-price="displayPrice"
            :show-original-price="showOriginalPrice" :flash-sale-name="flashSaleName" :flash-sale-price="flashSalePrice"
            :flash-sale-end-time="flashSaleEndTime" :flash-sale-sold="flashSaleSold"
            :flash-sale-quantity="flashSaleQuantity" :review-stats="reviewStats" :show-review-form="showReviewForm"
            :is-authenticated="isAuthenticated" :user-has-reviewed="userHasReviewed" :user-review="userReview"
            :review-form="reviewForm" :editing-review-id="editingReviewId" :is-submitting="isSubmitting"
            :preview-images="previewImages" :reviews-loading="reviewsLoading" :reviews="reviews"
            :review-pagination-data="reviewPaginationData" :total-review-pages="totalReviewPages"
            :total-reviews="totalReviews" :reviews-per-page="reviewsPerPage" :current-review-page="currentReviewPage"
            :user="user" :product-inventory="productInventory" :is-adding-to-cart="isAddingToCart"
            :cart-quantity="cartQuantity" @update:selectedSize="val => selectedSize = val"
            @update:selectedColor="val => selectedColor = val" v-model:activeTab="activeTab"
            @submitReview="submitReview" @update:showReviewForm="val => showReviewForm = val"
            @update:reviewForm="val => reviewForm = val" @removeImage="removeImage"
            @handleImageUpload="handleImageUpload" @add-to-cart="handleAddToCart" @cancelEdit="cancelEdit"
            @editReview="editReview" @removeReview="removeReview" @handleReviewPageChange="handleReviewPageChange"
            :related-products="relatedProducts" @variantChange="handleVariantChange"
            @update:mainImage="handleMainImageUpdate" />
        <div class="max-w-7xl mx-auto">
            <RecentlyViewed variant="detail" />
        </div>
    </template>
    <div v-else class="flex flex-col items-center justify-center h-screen">
        <div class="mb-4">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-[#81AACC]"></div>
        </div>
        <p class="text-gray-600 text-lg">Đang tải sản phẩm...</p>
    </div>

</template>

<script setup>
import { ref, onMounted, nextTick, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useHead } from '@vueuse/head'
import ProductsDetail from '../components/products/ProductsDetail.vue'
import RecentlyViewed from '../components/home/RecentlyViewed.vue'
import { useProducts } from '../composable/useProducts'
import { useInventories } from '../composable/useInventorie'
import { useReviews } from '../composable/useReviews'
import { useAuth } from '../composable/useAuth'
import { useCart } from '../composable/useCart'
import { useRecentlyViewed } from '../composable/useRecentlyViewed'
import { push } from 'notivue'

const route = useRoute()
const { getProductBySlug, getProducts } = useProducts()
const { getInventories } = useInventories()
const { getReviewsByProductSlug, addReview, updateReview, deleteReview, checkUserReview } = useReviews()
const { user, isAuthenticated } = useAuth()
const { addToCart, cart } = useCart()
const { addToRecentlyViewed } = useRecentlyViewed()

const product = ref(null)
const productInventory = ref([])
const mainImage = ref('')
const selectedSize = ref('')
const selectedColor = ref(null)
const quantity = ref(1)
const selectedVariantStock = ref(0)
const displayPrice = ref(0)
const showOriginalPrice = ref(false)
const isAddingToCart = ref(false)

// Thêm biến để lưu tất cả ảnh sản phẩm
const allProductImages = ref([])

const flashSaleName = ref('')
const flashSalePrice = ref(0)
const flashSaleEndTime = ref('')
const flashSaleSold = ref(0)
const flashSaleQuantity = ref(0)

const activeTab = ref('description')

// REVIEW STATES
const reviewStats = ref({ average: 0, total: 0, distribution: [] })
const showReviewForm = ref(false)
const userHasReviewed = ref(false)
const userReview = ref(null)
const reviewForm = ref({ rating: 5, content: '', images: [] })
const editingReviewId = ref(null)
const isSubmitting = ref(false)
const previewImages = ref([])
const deleteImageIds = ref([])
const reviewsLoading = ref(false)
const reviews = ref([])
const reviewPaginationData = ref(null)
const totalReviewPages = ref(1)
const totalReviews = ref(0)
const reviewsPerPage = ref(3)
const currentReviewPage = ref(1)

useHead(() => {
    if (!product.value) return {}
    return {
        title: `${product.value.name} | DEVGANG`,
        meta: [
            {
                name: 'description',
                content: product.value.description?.substring(0, 160) || 'Chi tiết sản phẩm từ DEVGANG'
            }
        ]
    }
})

// Computed property để lấy số lượng sản phẩm đã có trong giỏ hàng cho biến thể hiện tại
const cartQuantity = computed(() => {
    if (!selectedSize.value || !selectedColor.value?.name || !cart.value) return 0

    // Tìm biến thể trong giỏ hàng
    const cartItem = cart.value.find(item => {
        return item.variant?.size === selectedSize.value &&
            item.variant?.color === selectedColor.value.name &&
            item.variant?.product?.id === product.value?.id
    })

    return cartItem ? cartItem.quantity : 0
})

const fetchReviews = async (page = 1) => {
    if (!product.value) return
    try {
        reviewsLoading.value = true
        const userId = isAuthenticated.value && user.value?.id
        const res = await getReviewsByProductSlug(product.value.slug, page, reviewsPerPage.value, userId)

        currentReviewPage.value = res.current_page
        totalReviewPages.value = res.last_page
        totalReviews.value = res.total
        reviewPaginationData.value = {
            current_page: res.current_page,
            last_page: res.last_page,
            per_page: res.per_page,
            total: res.total,
            from: res.from,
            to: res.to
        }

        reviews.value = res.data.filter(review => {
            return review.is_approved && !review.is_hidden || (userId && review.user_id === userId)
        })

        const total = reviews.value.length
        const sum = reviews.value.reduce((acc, r) => acc + r.rating, 0)
        const average = total ? sum / total : 0

        reviewStats.value = {
            average: parseFloat(average.toFixed(1)),
            total,
            distribution: [5, 4, 3, 2, 1].map(star => {
                const count = reviews.value.filter(r => r.rating === star).length
                return {
                    stars: star,
                    percentage: total ? Math.round((count / total) * 100) : 0
                }
            })
        }

        if (isAuthenticated.value && user.value) {
            const result = await checkUserReview(user.value.id, product.value.slug)
            userHasReviewed.value = result.hasReviewed
            userReview.value = result.review || null
        }
    } catch (err) {
        console.error('Lỗi khi tải đánh giá:', err)
    } finally {
        reviewsLoading.value = false
    }
}

const handleImageUpload = (event) => {

    if (!Array.isArray(reviewForm.value.images)) {
        reviewForm.value.images = []
    }

    if (!Array.isArray(previewImages.value)) {
        previewImages.value = []
    }

    const files = event.target.files

    if (!files || files.length === 0) {
        return
    }

    Array.from(files).forEach((file, index) => {

        reviewForm.value.images.push(file)

        if (!Array.isArray(previewImages.value)) {
            previewImages.value = []
        }

        const reader = new FileReader()
        reader.onload = e => {
            previewImages.value.push({
                file,
                url: e.target.result,
                existing: false
            })
        }
        reader.readAsDataURL(file)
    })

}

const removeImage = (index) => {

    if (!Array.isArray(previewImages.value)) {
        previewImages.value = []
        return
    }

    const imageToRemove = previewImages.value[index]

    if (imageToRemove?.existing && imageToRemove?.id) {
        deleteImageIds.value.push(imageToRemove.id)
    } else {
        if (!Array.isArray(reviewForm.value.images)) {
            reviewForm.value.images = []
        }

        const fileIndex = reviewForm.value.images.findIndex(img => img === imageToRemove.file)
        if (fileIndex !== -1) {
            reviewForm.value.images.splice(fileIndex, 1)
        }
    }

    previewImages.value.splice(index, 1)
}

const submitReview = async () => {
    if (!product.value || !isAuthenticated.value || !reviewForm.value.content.trim()) return
    isSubmitting.value = true
    if (!Array.isArray(reviewForm.value.images)) {
        reviewForm.value.images = []
    }

    const payload = {
        user_id: user.value.id,
        product_slug: product.value.slug,
        rating: reviewForm.value.rating,
        content: reviewForm.value.content,
        images: reviewForm.value.images,
        ...(editingReviewId.value && { delete_image_ids: deleteImageIds.value })
    }

    try {
        if (editingReviewId.value) {
            await updateReview(editingReviewId.value, payload)
        } else {
            await addReview(payload)
        }

        reviewForm.value = { rating: 5, content: '', images: [] }
        editingReviewId.value = null
        previewImages.value = []
        deleteImageIds.value = []
        showReviewForm.value = false

        if (!Array.isArray(reviewForm.value.images)) {
            reviewForm.value.images = []
        }

        if (!Array.isArray(previewImages.value)) {
            previewImages.value = []
        }

        await fetchReviews(1)
    } catch (e) {
        console.error('Lỗi khi gửi đánh giá:', e)
        console.error('Error details:', e.response?.data)
        console.error('Error status:', e.response?.status)
        console.error('Error headers:', e.response?.headers)
        console.error('Full error object:', e)
    } finally {
        isSubmitting.value = false
    }
}

const editReview = (review) => {
    console.log('editReview called with:', review)

    editingReviewId.value = review.id
    reviewForm.value = {
        rating: review.rating,
        content: review.content,
        images: []
    }

    deleteImageIds.value = []

    if (!Array.isArray(reviewForm.value.images)) {
        reviewForm.value.images = []
    }

    if (!Array.isArray(previewImages.value)) {
        previewImages.value = []
    }

    previewImages.value = review.images.map(img => ({
        url: `${import.meta.env.VITE_API_BASE_URL}/storage/${img.image_path}`,
        id: img.id,
        existing: true,
        file: null
    }))

    showReviewForm.value = true
    console.log('After editReview setup:')
    console.log('editingReviewId:', editingReviewId.value)
    console.log('showReviewForm:', showReviewForm.value)
    console.log('reviewForm:', reviewForm.value)
}

const cancelEdit = () => {

    editingReviewId.value = null
    reviewForm.value = { rating: 5, content: '', images: [] }
    previewImages.value = []
    deleteImageIds.value = []
    showReviewForm.value = false

    if (!Array.isArray(reviewForm.value.images)) {
        reviewForm.value.images = []
    }

    if (!Array.isArray(previewImages.value)) {
        previewImages.value = []
    }

}

const removeReview = async (id) => {
    if (confirm('Bạn chắc chắn muốn xóa đánh giá này?')) {
        await deleteReview(id)
        await fetchReviews(currentReviewPage.value)
    }
}

const handleReviewPageChange = (page) => {
    currentReviewPage.value = page
    fetchReviews(page)
}

const handleAddToCart = async () => {
    try {
        isAddingToCart.value = true

        const selectedVariant = product.value.variants?.find(v =>
            String(v.size) === String(selectedSize.value) &&
            String(v.color) === String(selectedColor.value?.name)
        )

        if (!selectedVariant) {
            push.error('Không tìm thấy biến thể sản phẩm phù hợp')
            return
        }

        let maxAvailable = Number(selectedVariantStock.value || 0)
        let isFlashSale = false

        if (flashSalePrice.value && Number(flashSalePrice.value) > 0) {
            isFlashSale = true
            const fsQuantity = Number(flashSaleQuantity.value || 0)

            if (fsQuantity <= 0) {
                push.warning('Sản phẩm Flash Sale đã hết hàng')
                return
            }

            const currentCartQuantity = cartQuantity.value
            const totalRequested = currentCartQuantity + quantity.value

            if (totalRequested > fsQuantity) {
                push.warning(`Số lượng vượt quá cho phép. Flash Sale còn lại: ${fsQuantity}, đã có trong giỏ: ${currentCartQuantity}`)
                return
            }

            maxAvailable = Math.min(maxAvailable, fsQuantity - currentCartQuantity)
        } else {
            const currentCartQuantity = cartQuantity.value
            const totalRequested = currentCartQuantity + quantity.value

            if (totalRequested > maxAvailable) {
                push.warning(`Số lượng vượt quá cho phép. Tối đa còn lại: ${maxAvailable}, đã có trong giỏ: ${currentCartQuantity}`)
                return
            }

            maxAvailable = maxAvailable - currentCartQuantity
        }

        if (quantity.value > maxAvailable) {
            push.warning(`Số lượng vượt quá cho phép. Tối đa còn lại: ${maxAvailable}`)
            return
        }

        let price = selectedVariant.price
        if (isFlashSale && product.value?.price) {
            price = Number(flashSalePrice.value)
        }

        const added = await addToCart(selectedVariant.id, quantity.value, price)
        if (added?.flash_sale && typeof added.flash_sale.remaining === 'number') {
            push.success(`Đã thêm vào giỏ hàng. Flash Sale còn lại: ${added.flash_sale.remaining}`)
        } else {
            push.success('Đã thêm vào giỏ hàng')
        }
    } catch (error) {
        console.error('Lỗi khi thêm vào giỏ hàng:', error)
        push.error('Có lỗi xảy ra khi thêm vào giỏ hàng')
    } finally {
        isAddingToCart.value = false
    }
}

const handleMainImageUpdate = (newImagePath) => {
    mainImage.value = newImagePath
}

const handleVariantChange = (variantData) => {
    const { size, color } = variantData

    selectedSize.value = size
    selectedColor.value = { name: color }

    const foundVariant = product.value.variants.find(v =>
        v.size === size && v.color === color
    )

    if (foundVariant) {
        displayPrice.value = foundVariant.price
        const inventory = productInventory.value.find(inv =>
            (inv.variant_id && inv.variant_id === foundVariant.id) ||
            (inv.size === foundVariant.size && inv.color === foundVariant.color)
        )
        selectedVariantStock.value = inventory ? inventory.quantity : 0

    } else {
        const sizeOnlyVariant = product.value.variants.find(v => v.size === size)
        if (sizeOnlyVariant) {
            displayPrice.value = sizeOnlyVariant.price
            const inventory = productInventory.value.find(inv =>
                (inv.variant_id && inv.variant_id === sizeOnlyVariant.id) ||
                (inv.size === sizeOnlyVariant.size && inv.color === sizeOnlyVariant.color)
            )
            selectedVariantStock.value = inventory ? inventory.quantity : 0
        }
    }

    quantity.value = 1
};

const generateAllProductImages = () => {
    if (!product.value) return []

    const images = []

    if (product.value.images && product.value.images.length > 0) {
        images.push(...product.value.images.map(img => ({
            ...img,
            type: 'main',
            source: 'product'
        })))
    }

    if (product.value.variants && product.value.variants.length > 0) {
        product.value.variants.forEach(variant => {
            if (variant.images && variant.images.length > 0) {
                variant.images.forEach(img => {
                    const exists = images.some(existingImg =>
                        existingImg.image_path === img.image_path
                    )
                    if (!exists) {
                        images.push({
                            ...img,
                            type: 'variant',
                            source: 'variant',
                            variantSize: variant.size,
                            variantColor: variant.color
                        })
                    }
                })
            }
        })
    }

    return images
}

const relatedProducts = ref([])
const fetchRelatedProducts = async () => {
    if (product.value?.categories_id) {
        try {
            const result = await getProducts()
            const products = result.products || []
            relatedProducts.value = products
                .filter(p => p.categories_id === product.value.categories_id && p.id !== product.value.id)
                .slice(0, 5)
        } catch (error) {
            console.error('Error fetching related products:', error)
        }
    }
}

async function loadProduct(slug) {
    try {
        const data = await getProductBySlug(slug)

        product.value = data

        mainImage.value = data.images?.[0]?.image_path || ''
        displayPrice.value = data.price

        if (data.variants && data.variants.length > 0) {
            const firstVariant = data.variants[0]

            selectedSize.value = firstVariant.size
            selectedColor.value = { name: firstVariant.color }

            displayPrice.value = firstVariant.price
            const inventory = await getInventories({ product_id: data.id })
            productInventory.value = inventory

            const firstInventory = inventory.find(inv =>
                (inv.variant_id && inv.variant_id === firstVariant.id) ||
                (inv.size === firstVariant.size && inv.color === firstVariant.color)
            )
            selectedVariantStock.value = firstInventory ? firstInventory.quantity : 0

            nextTick(() => {
                handleVariantChange({ size: firstVariant.size, color: firstVariant.color })
            })
        } else {
            const inventories = await getInventories({ product_id: data.id })
            productInventory.value = inventories
        }

        allProductImages.value = generateAllProductImages()

        if (route.query.flashsale) {
            flashSaleName.value = route.query.flashsale
        }
        if (route.query.flash_price) {
            flashSalePrice.value = Number(route.query.flash_price)
        }
        if (route.query.end_time) {
            flashSaleEndTime.value = route.query.end_time
        }
        if (route.query.sold) {
            flashSaleSold.value = Number(route.query.sold)
        }
        if (route.query.quantity) {
            flashSaleQuantity.value = Number(route.query.quantity)
        }

        await fetchReviews()
        await fetchRelatedProducts()
        await addToRecentlyViewed(product.value)
    } catch (err) {
        console.error('Lỗi khi tải sản phẩm:', err)
    }
}

onMounted(async () => {
    const slug = route.params.slug
    if (!slug) return
    await loadProduct(slug)
})

watch(() => route.params.slug, async (newSlug, oldSlug) => {
    if (!newSlug || newSlug === oldSlug) return
    activeTab.value = 'description'
    quantity.value = 1
    previewImages.value = []
    deleteImageIds.value = []
    await loadProduct(newSlug)
    window.scrollTo({ top: 0, behavior: 'smooth' })
})
</script>