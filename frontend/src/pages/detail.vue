<template>
    <ProductsDetail v-if="product" :product="product" :product-images="product.images || []" :main-image="mainImage"
        :selected-size="selectedSize" :selected-color="selectedColor" v-model:quantity="quantity"
        :selected-variant-stock="selectedVariantStock" :display-price="displayPrice"
        :show-original-price="showOriginalPrice" :flash-sale-name="flashSaleName" :flash-sale-price="flashSalePrice"
        :flash-sale-end-time="flashSaleEndTime" :flash-sale-sold="flashSaleSold"
        :flash-sale-quantity="flashSaleQuantity" :flash-sale-percent="flashSalePercent" :review-stats="reviewStats"
        :show-review-form="showReviewForm" :is-authenticated="isAuthenticated" :user-has-reviewed="userHasReviewed"
        :user-review="userReview" :review-form="reviewForm" :editing-review-id="editingReviewId"
        :is-submitting="isSubmitting" :preview-images="previewImages" :reviews-loading="reviewsLoading"
        :reviews="reviews" :review-pagination-data="reviewPaginationData" :total-review-pages="totalReviewPages"
        :total-reviews="totalReviews" :reviews-per-page="reviewsPerPage" :current-review-page="currentReviewPage"
        :user="user" :product-inventory="productInventory" @update:selectedSize="val => selectedSize = val"
        @update:selectedColor="val => selectedColor = val" v-model:activeTab="activeTab" @submitReview="submitReview"
        @update:showReviewForm="val => showReviewForm = val" @update:reviewForm="val => reviewForm = val"
        @removeImage="removeImage" @handleImageUpload="handleImageUpload" @add-to-cart="handleAddToCart"
        @cancelEdit="cancelEdit" @editReview="editReview" @removeReview="removeReview"
        @handleReviewPageChange="handleReviewPageChange" :related-products="relatedProducts" />
    <div v-else class="text-center py-10 mt-10">Đang tải sản phẩm...</div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useHead } from '@vueuse/head'
import ProductsDetail from '../components/products/ProductsDetail.vue'
import { useProducts } from '../composable/useProducts'
import { useInventories } from '../composable/useInventorie'
import { useReviews } from '../composable/useReviews'
import { useAuth } from '../composable/useAuth'
import { useCart } from '../composable/useCart'
import { push } from 'notivue'

const route = useRoute()
const { getProductBySlug, getProducts } = useProducts()
const { getInventories } = useInventories()
const { getReviewsByProductSlug, addReview, updateReview, deleteReview, checkUserReview } = useReviews()
const { user, isAuthenticated } = useAuth()
const { addToCart } = useCart()

const product = ref(null)
const productInventory = ref([])
const mainImage = ref('')
const selectedSize = ref('')
const selectedColor = ref(null)
const quantity = ref(1)
const selectedVariantStock = ref(0)
const displayPrice = ref(0)
const showOriginalPrice = ref(false)

const flashSaleName = ref('')
const flashSalePrice = ref(0)
const flashSaleEndTime = ref('')
const flashSaleSold = ref(0)
const flashSaleQuantity = ref(0)
const flashSalePercent = ref(0)

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
    console.log('handleImageUpload called with event:', event)
    console.log('Event type:', event.type)
    console.log('Event target:', event.target)
    console.log('Event target type:', event.target?.type)
    
    // Ensure reviewForm.value.images is an array at the beginning
    if (!Array.isArray(reviewForm.value.images)) {
        console.log('reviewForm.value.images is not an array at the beginning, initializing...')
        reviewForm.value.images = []
    }
    
    // Ensure previewImages.value is an array at the beginning
    if (!Array.isArray(previewImages.value)) {
        console.log('previewImages.value is not an array at the beginning, initializing...')
        previewImages.value = []
    }
    
    const files = event.target.files
    console.log('Files from event:', files)

    if (!files || files.length === 0) {
        console.log('No files selected')
        console.log('Files object:', files)
        console.log('Files type:', typeof files)
        console.log('Files constructor:', files?.constructor?.name)
        return
    }

    console.log('Processing files:', Array.from(files))

    Array.from(files).forEach((file, index) => {
        console.log(`Processing file ${index}:`, file)
        console.log(`File type: ${file.type}, size: ${file.size}`)

        // Ensure reviewForm.value.images is an array
        if (!Array.isArray(reviewForm.value.images)) {
            console.log('reviewForm.value.images is not an array, initializing...')
            reviewForm.value.images = []
        }

        reviewForm.value.images.push(file)
        
        // Ensure previewImages.value is an array
        if (!Array.isArray(previewImages.value)) {
            console.log('previewImages.value is not an array, initializing...')
            previewImages.value = []
        }
        
        const reader = new FileReader()
        reader.onload = e => {
            console.log(`File ${index} loaded, result:`, e.target.result.substring(0, 100) + '...')
            previewImages.value.push({
                file,
                url: e.target.result,
                existing: false // Thêm thuộc tính existing: false cho ảnh mới
            })
        }
        reader.readAsDataURL(file)
    })

    console.log('Final reviewForm.images:', reviewForm.value.images)
    console.log('Final previewImages:', previewImages.value)
}

const removeImage = (index) => {
    console.log('Removing image at index:', index)
    
    // Ensure previewImages.value is an array
    if (!Array.isArray(previewImages.value)) {
        console.log('previewImages.value is not an array, initializing...')
        previewImages.value = []
        return
    }
    
    console.log('Image to remove:', previewImages.value[index])

    const imageToRemove = previewImages.value[index]

    // Kiểm tra xem ảnh có phải là ảnh cũ không
    if (imageToRemove?.existing && imageToRemove?.id) {
        console.log('Removing existing image with ID:', imageToRemove.id)
        deleteImageIds.value.push(imageToRemove.id)
        console.log('Added to deleteImageIds:', deleteImageIds.value)
    } else {
        // Nếu là ảnh mới, xóa khỏi reviewForm.images
        console.log('Removing new image file')
        
        // Ensure reviewForm.value.images is an array
        if (!Array.isArray(reviewForm.value.images)) {
            console.log('reviewForm.value.images is not an array, initializing...')
            reviewForm.value.images = []
        }
        
        const fileIndex = reviewForm.value.images.findIndex(img => img === imageToRemove.file)
        console.log('File index in reviewForm.images:', fileIndex)
        if (fileIndex !== -1) {
            reviewForm.value.images.splice(fileIndex, 1)
            console.log('File removed from reviewForm.images')
        }
    }

    // Xóa ảnh khỏi previewImages
    previewImages.value.splice(index, 1)
    console.log('Image removed from previewImages')
    console.log('Updated reviewForm.images:', reviewForm.value.images)
    console.log('Updated previewImages:', previewImages.value)
}

const submitReview = async () => {
    if (!product.value || !isAuthenticated.value || !reviewForm.value.content.trim()) return
    isSubmitting.value = true
    // Ensure reviewForm.value.images is an array
    if (!Array.isArray(reviewForm.value.images)) {
        console.log('reviewForm.value.images is not an array in submitReview, initializing...')
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

    console.log('Submitting review with payload:', payload)
    console.log('Images in payload:', payload.images)
    console.log('Delete image IDs:', deleteImageIds.value)
    console.log('Editing review ID:', editingReviewId.value)

    try {
        if (editingReviewId.value) {
            await updateReview(editingReviewId.value, payload)
        } else {
            await addReview(payload)
        }
        
        console.log('Review submitted successfully')
        
        // Reset form only on success
        reviewForm.value = { rating: 5, content: '', images: [] }
        editingReviewId.value = null
        previewImages.value = []
        deleteImageIds.value = []
        showReviewForm.value = false
        
        // Ensure reviewForm.value.images is an array after reset
        if (!Array.isArray(reviewForm.value.images)) {
            console.log('reviewForm.value.images is not an array after reset, initializing...')
            reviewForm.value.images = []
        }
        
        // Ensure previewImages.value is an array after reset
        if (!Array.isArray(previewImages.value)) {
            console.log('previewImages.value is not an array after reset, initializing...')
            previewImages.value = []
        }
        
        console.log('Reset deleteImageIds after successful submission:', deleteImageIds.value)
        await fetchReviews(1)
    } catch (e) {
        console.error('Lỗi khi gửi đánh giá:', e)
        console.error('Error details:', e.response?.data)
        console.error('Error status:', e.response?.status)
        console.error('Error headers:', e.response?.headers)
        console.error('Full error object:', e)
        // Don't reset form on error - let user retry
    } finally {
        isSubmitting.value = false
    }
}

const editReview = (review) => {
    console.log('Editing review:', review)
    console.log('Review images:', review.images)

    editingReviewId.value = review.id
    reviewForm.value = {
        rating: review.rating,
        content: review.content,
        images: [] // Array rỗng cho ảnh mới
    }
    
    // Reset deleteImageIds when editing
    deleteImageIds.value = []
    console.log('Reset deleteImageIds for editing:', deleteImageIds.value)

    // Ensure reviewForm.value.images is an array
    if (!Array.isArray(reviewForm.value.images)) {
        console.log('reviewForm.value.images is not an array in editReview, initializing...')
        reviewForm.value.images = []
    }

    // Ensure previewImages.value is an array
    if (!Array.isArray(previewImages.value)) {
        console.log('previewImages.value is not an array in editReview, initializing...')
        previewImages.value = []
    }

    // Xử lý ảnh cũ
    previewImages.value = review.images.map(img => ({
        url: `${import.meta.env.VITE_API_BASE_URL}/storage/${img.image_path}`,
        id: img.id,
        existing: true,
        file: null // Không có file cho ảnh cũ
    }))

    console.log('Updated reviewForm:', reviewForm.value)
    console.log('Updated previewImages:', previewImages.value)

    showReviewForm.value = true
}

const cancelEdit = () => {
    console.log('Canceling edit, resetting form state')
    
    editingReviewId.value = null
    reviewForm.value = { rating: 5, content: '', images: [] }
    previewImages.value = []
    deleteImageIds.value = []
    showReviewForm.value = false
    
    // Ensure reviewForm.value.images is an array
    if (!Array.isArray(reviewForm.value.images)) {
        console.log('reviewForm.value.images is not an array in cancelEdit, initializing...')
        reviewForm.value.images = []
    }
    
    // Ensure previewImages.value is an array
    if (!Array.isArray(previewImages.value)) {
        console.log('previewImages.value is not an array in cancelEdit, initializing...')
        previewImages.value = []
    }
    
    console.log('Reset deleteImageIds in cancelEdit:', deleteImageIds.value)
    
    console.log('Form state reset completed')
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
        console.log('selectedSize:', selectedSize.value)
        console.log('selectedColor:', selectedColor.value)
        console.log('variants:', product.value.variants)

        const selectedVariant = product.value.variants?.find(v =>
            String(v.size) === String(selectedSize.value) &&
            String(v.color) === String(selectedColor.value?.name)
        )

        console.log('selectedVariant:', selectedVariant)

        if (!selectedVariant) {
            push.error('Không tìm thấy biến thể sản phẩm phù hợp')
            return
        }

        if (quantity.value > selectedVariant.stock) {
            push.warning('Số lượng vượt quá số lượng trong kho')
            return
        }

        let price = selectedVariant.price
        if (flashSalePrice.value && product.value.price) {
            const percent = Math.round(100 - (flashSalePrice.value / product.value.price) * 100)
            if (percent > 0) {
                price = Math.round(selectedVariant.price * (1 - percent / 100))
            }
        }

        await addToCart(selectedVariant.id, quantity.value, price)
        push.success('Đã thêm vào giỏ hàng')
    } catch (error) {
        console.error('Lỗi khi thêm vào giỏ hàng:', error)
        push.error('Có lỗi xảy ra khi thêm vào giỏ hàng')
    }
}


const relatedProducts = ref([])
const fetchRelatedProducts = async () => {
    if (product.value?.categories_id) {
        try {
            const products = await getProducts()
            relatedProducts.value = products
                .filter(p => p.categories_id === product.value.categories_id && p.id !== product.value.id)
                .slice(0, 5)
            console.log('relatedProducts:', relatedProducts.value)

        } catch (error) {
            console.error('Error fetching related products:', error)
        }
    }
}

onMounted(async () => {
    const slug = route.params.slug
    if (!slug) return
    try {
        const data = await getProductBySlug(slug)
        product.value = data
        mainImage.value = data.images?.[0]?.image_path || ''
        displayPrice.value = data.price

        // LẤY GIÁ TRỊ FLASH SALE TỪ QUERY
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

        const inventories = await getInventories({ product_id: data.id })
        productInventory.value = inventories

        await fetchReviews()
        await fetchRelatedProducts()
    } catch (err) {
        console.error('Lỗi khi tải sản phẩm:', err)
    }
})
</script>

<style scoped lang="scss"></style>
