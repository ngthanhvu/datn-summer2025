<template>
    <div class="tw-mt-8">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <h2 class="tw-text-2xl tw-font-bold tw-text-gray-800">Đánh giá gần nhất</h2>
            <NuxtLink to="/reviews" class="tw-text-blue-600 tw-hover:text-blue-800 tw-font-medium tw-transition-colors">
                Xem tất cả →
            </NuxtLink>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
            <div v-for="i in 3" :key="i" class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-animate-pulse">
                <div class="tw-flex tw-items-center tw-mb-4">
                    <div class="tw-w-12 tw-h-12 tw-bg-gray-200 tw-rounded-full tw-mr-3"></div>
                    <div class="tw-flex-1">
                        <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-mb-1"></div>
                        <div class="tw-h-3 tw-bg-gray-200 tw-rounded"></div>
                    </div>
                </div>
                <div class="tw-h-16 tw-bg-gray-200 tw-rounded tw-mb-4"></div>
                <div class="tw-h-4 tw-bg-gray-200 tw-rounded tw-mb-2"></div>
                <div class="tw-h-3 tw-bg-gray-200 tw-rounded"></div>
            </div>
        </div>

        <!-- Reviews Grid -->
        <div v-else class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6">
            <div v-for="review in latestReviews" :key="review.id"
                class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-hover:shadow-md tw-transition-shadow">
                <!-- User Info -->
                <div class="tw-flex tw-items-center tw-mb-4">
                    <img :src="getUserAvatar(review.user)" :alt="review.user?.name || 'User'"
                        class="tw-w-12 tw-h-12 tw-rounded-full tw-object-cover tw-mr-3" @error="handleImageError" />
                    <div class="tw-flex-1">
                        <h4 class="tw-font-medium tw-text-gray-800">{{ review.user?.name || 'Khách hàng' }}</h4>
                        <p class="tw-text-sm tw-text-gray-500">{{ formatDate(review.created_at) }}</p>
                    </div>
                    <div class="tw-flex tw-items-center">
                        <svg v-for="star in 5" :key="star" class="tw-w-4 tw-h-4"
                            :class="star <= review.rating ? 'tw-text-yellow-400' : 'tw-text-gray-300'"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- Product Info -->
                <div v-if="review.product" class="tw-flex tw-items-center tw-mb-3 tw-p-3 tw-bg-gray-50 tw-rounded-lg">
                    <img :src="getProductImage(review.product)" :alt="review.product.name"
                        class="tw-w-12 tw-h-12 tw-rounded tw-object-cover tw-mr-3" @error="handleImageError" />
                    <div class="tw-flex-1">
                        <h5 class="tw-font-medium tw-text-gray-800 tw-text-sm">{{ review.product.name }}</h5>
                        <p class="tw-text-xs tw-text-gray-500">{{ formatPrice(review.product.price) }}</p>
                    </div>
                </div>

                <!-- Review Content -->
                <div class="tw-mb-4">
                    <h6 class="tw-font-medium tw-text-gray-800 tw-mb-2">{{ review.title || 'Đánh giá sản phẩm' }}</h6>
                    <p class="tw-text-gray-600 tw-text-sm tw-leading-relaxed">
                        {{ review.comment }}
                    </p>
                </div>

                <!-- Review Images -->
                <div v-if="review.images && review.images.length > 0" class="tw-flex tw-gap-2 tw-mb-4">
                    <img v-for="(image, index) in review.images.slice(0, 3)" :key="index"
                        :src="image.image_path || image" alt="Review image"
                        class="tw-w-16 tw-h-16 tw-rounded tw-object-cover" @error="handleImageError" />
                    <div v-if="review.images.length > 3"
                        class="tw-w-16 tw-h-16 tw-bg-gray-200 tw-rounded tw-flex tw-items-center tw-justify-center">
                        <span class="tw-text-xs tw-text-gray-500">+{{ review.images.length - 3 }}</span>
                    </div>
                </div>

                <!-- Review Actions -->
                <div class="tw-flex tw-items-center tw-justify-between tw-pt-3 tw-border-t tw-border-gray-100">
                    <div class="tw-flex tw-items-center tw-gap-4">
                        <button
                            class="tw-flex tw-items-center tw-gap-1 tw-text-sm tw-text-gray-500 tw-hover:text-blue-600 tw-transition-colors">
                            <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5">
                                </path>
                            </svg>
                            {{ review.likes_count || 0 }}
                        </button>
                        <button
                            class="tw-flex tw-items-center tw-gap-1 tw-text-sm tw-text-gray-500 tw-hover:text-blue-600 tw-transition-colors">
                            <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                </path>
                            </svg>
                            {{ review.replies_count || 0 }}
                        </button>
                    </div>
                    <button class="tw-text-sm tw-text-blue-600 tw-hover:text-blue-800 tw-font-medium">
                        Trả lời
                    </button>
                </div>
            </div>
        </div>

        <!-- Review Stats -->
        <div v-if="reviewStats"
            class="tw-mt-8 tw-bg-gradient-to-r tw-from-green-500 tw-to-blue-500 tw-rounded-lg tw-p-6 tw-text-white">
            <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6 tw-text-center">
                <div>
                    <div class="tw-text-3xl tw-font-bold tw-mb-2">{{ reviewStats.totalReviews }}</div>
                    <p class="tw-text-green-100">Tổng đánh giá</p>
                </div>
                <div>
                    <div class="tw-text-3xl tw-font-bold tw-mb-2">{{ reviewStats.averageRating }}/5</div>
                    <p class="tw-text-green-100">Điểm trung bình</p>
                </div>
                <div>
                    <div class="tw-text-3xl tw-font-bold tw-mb-2">{{ reviewStats.verifiedReviews }}</div>
                    <p class="tw-text-green-100">Đánh giá xác thực</p>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && latestReviews.length === 0" class="tw-text-center tw-py-8">
            <p class="tw-text-gray-500">Chưa có đánh giá nào</p>
        </div>
    </div>
</template>

<script setup>
import { useHome } from '../../composables/useHome'

const { getLatestReviews, getReviewStats, formatPrice, formatDate } = useHome()

const latestReviews = ref([])
const reviewStats = ref(null)
const loading = ref(true)

// Lấy đánh giá gần nhất
const fetchLatestReviews = async () => {
    try {
        loading.value = true
        const reviews = await getLatestReviews(6)
        latestReviews.value = reviews
    } catch (error) {
        console.error('Error fetching latest reviews:', error)
    } finally {
        loading.value = false
    }
}

// Lấy thống kê đánh giá
const fetchReviewStats = async () => {
    try {
        const stats = await getReviewStats()
        reviewStats.value = stats
    } catch (error) {
        console.error('Error fetching review stats:', error)
    }
}

// Lấy avatar người dùng
const getUserAvatar = (user) => {
    if (user?.avatar) {
        return user.avatar.startsWith('http') ? user.avatar : `https://placehold.co/100x100?text=${user.name?.charAt(0) || 'U'}`
    }
    return `https://placehold.co/100x100?text=${user?.name?.charAt(0) || 'U'}`
}

// Lấy ảnh sản phẩm
const getProductImage = (product) => {
    if (product?.images && product.images.length > 0) {
        const mainImage = product.images.find(img => img.is_main)
        return mainImage ? mainImage.image_path : product.images[0].image_path
    }
    return `https://placehold.co/100x100?text=${product?.name?.charAt(0) || 'P'}`
}

// Xử lý lỗi ảnh
const handleImageError = (event) => {
    const alt = event.target.alt || 'Image'
    event.target.src = `https://placehold.co/100x100?text=${alt.charAt(0)}`
}

// Khởi tạo dữ liệu
onMounted(async () => {
    await Promise.all([
        fetchLatestReviews(),
        fetchReviewStats()
    ])
})
</script>