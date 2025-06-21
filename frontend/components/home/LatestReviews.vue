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
        <div v-else class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6 tw-mb-10">
            <div v-for="review in latestReviews" :key="review.id"
                class="tw-bg-white tw-rounded-lg tw-shadow-sm tw-p-6 tw-flex tw-flex-col tw-gap-2">
                <div class="tw-flex tw-justify-between tw-items-start tw-mb-2">
                    <div>
                        <div class="tw-font-semibold tw-text-lg tw-text-gray-800">{{ review.user?.name || 'Khách hàng'
                        }}</div>
                        <div class="tw-flex tw-items-center tw-mt-1">
                            <span v-for="star in 5" :key="star" class="tw-text-xl"
                                :class="star <= review.rating ? 'tw-text-yellow-500' : 'tw-text-gray-300'">★</span>
                        </div>
                    </div>
                    <img :src="getUserAvatar(review.user)" :alt="review.user?.name || 'User'"
                        class="tw-w-16 tw-h-16 tw-rounded tw-object-cover" @error="handleImageError" />
                </div>
                <div class="tw-text-gray-700 tw-mt-2">
                    {{ review.content }}
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