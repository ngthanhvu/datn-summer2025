<template>
    <div class="space-y-8">
        <!-- Review stats section -->
        <div class="bg-gray-50 rounded-lg p-6 shadow-sm transition-all hover:shadow-md">
            <div class="flex flex-col md:flex-row items-center gap-8">
                <div class="text-center rounded-lg p-4 min-w-[150px]">
                    <div class="text-5xl font-bold text-[#81AACC] mb-2">{{ reviewStats.average }}</div>
                    <div class="text-yellow-400 flex gap-1 justify-center mb-2">
                        <i v-for="n in 5" :key="n"
                            :class="n <= Math.round(reviewStats.average) ? 'bi bi-star-fill' : (n <= reviewStats.average + 0.5 ? 'bi bi-star-half' : 'bi bi-star')"
                            class="text-xl"></i>
                    </div>
                    <div class="text-sm text-gray-500 font-medium">{{ reviewStats.total }} đánh giá</div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-medium mb-4 text-center md:text-left">Phân bối đánh giá</h3>
                    <div v-for="rating in reviewStats.distribution" :key="rating.stars"
                        class="flex items-center gap-3 mb-3 group">
                        <span class="w-16 font-medium flex items-center gap-1">
                            {{ rating.stars }} <i class="bi bi-star-fill text-yellow-400"></i>
                        </span>
                        <div class="flex-1 h-3 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-yellow-400 rounded-full transition-all duration-500 group-hover:bg-yellow-500"
                                :style="{ width: rating.percentage + '%' }">
                            </div>
                        </div>
                        <span class="w-16 text-right font-medium">{{ rating.percentage }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Form -->
        <div id="review-form" v-if="showReviewForm"
            class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 mb-8 transition-all hover:shadow-md">
            <h3 class="text-xl font-semibold mb-6 flex items-center gap-2">
                <i class="bi bi-pencil-square text-[#81AACC]"></i>
                {{ editingReviewId ? 'Chỉnh sửa đánh giá' : 'Viết đánh giá' }}
            </h3>

            <div v-if="!isAuthenticated" class="text-center py-6 bg-gray-50 rounded-lg">
                <i class="bi bi-person-lock text-3xl text-gray-400 mb-2 block"></i>
                <p class="mb-4 text-gray-600">Vui lòng đăng nhập để đánh giá sản phẩm</p>
                <a href="/login"
                    class="bg-[#81AACC] text-white px-6 py-2 rounded-md inline-block font-medium transition-colors hover:bg-[#6B8BA3]">
                    <i class="bi bi-box-arrow-in-right mr-1"></i> Đăng nhập
                </a>
            </div>

            <form v-else @submit.prevent="$emit('submitReview')" class="space-y-6">
                <!-- Rating -->
                <div>
                    <label class="block mb-3 font-medium text-gray-700">Đánh giá của bạn</label>
                    <div class="flex text-3xl text-gray-300 mb-2">
                        <button v-for="star in 5" :key="star" type="button"
                            @click="$emit('update:reviewForm', { ...reviewForm, rating: star })"
                            class="focus:outline-none transition-colors duration-200 hover:text-yellow-400"
                            :class="star <= reviewForm.rating ? 'text-yellow-400' : ''">
                            <i class="bi bi-star-fill"></i>
                        </button>
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ ['Chọn đánh giá', 'Rất tệ', 'Tệ', 'Bình thường', 'Tốt', 'Rất tốt'][reviewForm.rating] }}
                    </div>
                </div>

                <!-- Content -->
                <div>
                    <label for="review-content" class="block mb-3 font-medium text-gray-700">Nội dung đánh giá</label>
                    <textarea id="review-content" :value="reviewForm.content"
                        @input="$emit('update:reviewForm', { ...reviewForm, content: $event.target.value })" rows="4"
                        class="w-full border border-gray-300 rounded-lg p-3 transition-colors focus:border-[#81AACC] focus:ring-2 focus:ring-[#81AACC]/20 focus:outline-none"
                        placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này" required></textarea>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block mb-3 font-medium text-gray-700">Hình ảnh (tùy chọn)</label>
                    <div
                        class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center transition-colors hover:border-[#81AACC] cursor-pointer relative">
                        <input type="file" @change="$emit('handleImageUpload', $event)" accept="image/*" multiple
                            class="absolute inset-0 opacity-0 cursor-pointer">
                        <i class="bi bi-cloud-arrow-up text-3xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">Kéo thả hoặc nhấp để tải lên hình ảnh</p>
                        <p class="text-xs text-gray-400 mt-1">Hỗ trợ JPG, PNG, GIF</p>
                    </div>

                    <!-- Image Previews -->
                    <div v-if="previewImages.length > 0" class="flex flex-wrap gap-3 mt-4">
                        <div v-for="(image, index) in previewImages" :key="index"
                            class="relative w-24 h-24 group overflow-hidden rounded-lg shadow-sm">
                            <img :src="image.url"
                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                            <div
                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300">
                            </div>
                            <button type="button" @click="$emit('removeImage', index)"
                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="bg-[#81AACC] text-white px-6 py-3 rounded-md font-medium transition-colors hover:bg-[#6B8BA3] flex items-center justify-center min-w-[150px]"
                        :disabled="isSubmitting">
                        <span v-if="isSubmitting">
                            <i class="bi bi-arrow-repeat animate-spin inline-block mr-2"></i> Đang xử lý...
                        </span>
                        <span v-else>
                            <i class="bi bi-send mr-2"></i> {{ editingReviewId ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}
                        </span>
                    </button>

                    <button type="button" @click="$emit('cancelEdit')"
                        class="bg-gray-200 text-gray-700 px-6 py-3 rounded-md font-medium transition-colors hover:bg-gray-300">
                        <i class="bi bi-x-lg mr-2"></i> {{ editingReviewId ? 'Hủy' : 'Đóng' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Review Form Toggle Button -->
        <div v-if="!showReviewForm && isAuthenticated && !userHasReviewed"
            class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 mb-8 text-center">
            <i class="bi bi-chat-square-text text-3xl text-gray-400 mb-3 block"></i>
            <p class="mb-4 text-gray-600">Bạn chưa đánh giá sản phẩm này</p>
            <button @click="$emit('update:showReviewForm', true)"
                class="bg-[#81AACC] text-white px-4 py-1.5 rounded-md font-medium transition-colors hover:bg-[#6B8BA3] inline-flex items-center gap-2 text-sm">
                <i class="bi bi-pencil-square"></i> Viết đánh giá
            </button>
        </div>

        <!-- Edit Review Button for users who have already reviewed -->
        <div v-if="!showReviewForm && isAuthenticated && userHasReviewed"
            class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 mb-8 text-center">
            <i class="bi bi-check-circle text-3xl text-green-500 mb-3 block"></i>
            <p class="mb-4 text-gray-600">Bạn đã đánh giá sản phẩm này rồi</p>
            <button @click="$emit('editReview', userReview)"
                class="bg-[#81AACC] text-white px-4 py-1.5 rounded-md font-medium transition-colors hover:bg-[#6B8BA3] inline-flex items-center gap-2 text-sm">
                <i class="bi bi-pencil"></i> Chỉnh sửa đánh giá của bạn
            </button>
        </div>

        <!-- Review List -->
        <div class="space-y-6">
            <h3 class="text-xl font-semibold mb-6 flex items-center gap-2">
                <i class="bi bi-chat-square-text text-[#81AACC]"></i> Đánh giá từ khách hàng
            </h3>

            <!-- Loading State -->
            <div v-if="reviewsLoading" class="text-center py-10 bg-gray-50 rounded-lg">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#81AACC] mb-4">
                </div>
                <p class="text-gray-500">Đang tải đánh giá...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="reviews.length === 0" class="text-center py-10 bg-gray-50 rounded-lg">
                <i class="bi bi-chat-square text-4xl text-gray-300 mb-3 block"></i>
                <p class="text-gray-500">Chưa có đánh giá nào cho sản phẩm này</p>
            </div>

            <!-- Reviews Content -->
            <div v-else class="space-y-6">
                <div v-for="review in reviews" :key="review.id"
                    class="bg-white rounded-lg p-6 border border-gray-100 shadow-sm transition-all hover:shadow-md">
                    <div class="flex justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <img :src="review.user?.avatar ? (review.user.avatar.startsWith('http') ? review.user.avatar : '/storage/avatars/' + review.user.avatar.split('/').pop()) : 'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'"
                                :alt="review.user?.name"
                                class="w-12 h-12 rounded-full object-cover border-2 border-gray-200" />
                            <div>
                                <div class="font-semibold text-gray-800">{{ review.user?.username || review.user?.name
                                }}</div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-sm text-gray-500 flex items-center gap-1">
                                        <i class="bi bi-calendar3"></i> {{ new
                                            Date(review.created_at).toLocaleDateString() }}
                                    </span>
                                    <span v-if="user && review.user_id === user.id">
                                        <span v-if="review.is_hidden"
                                            class="bg-red-100 text-red-600 text-xs font-semibold rounded-full px-3 py-1 ml-2">Vi
                                            phạm ngôn từ</span>
                                        <span v-else-if="!review.is_approved"
                                            class="bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full px-3 py-1 ml-2">Đang
                                            chờ duyệt</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="px-3 py-1 rounded-full flex items-center gap-1">
                                <div class="text-yellow-400">
                                    <i v-for="n in 5" :key="n"
                                        :class="n <= review.rating ? 'bi bi-star-fill' : 'bi bi-star'"
                                        class="text-sm"></i>
                                </div>
                            </div>
                            <!-- Nút sửa và xóa đánh giá -->
                            <div v-if="canModifyReview(review)" class="flex gap-2">
                                <button @click="$emit('editReview', review)"
                                    class="text-[#81AACC] hover:text-[#6B8BA3] bg-[#81AACC]/10 hover:bg-[#81AACC]/20 rounded-full w-8 h-8 flex items-center justify-center transition-colors"
                                    title="Chỉnh sửa đánh giá">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button @click="$emit('removeReview', review.id)"
                                    class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 rounded-full w-8 h-8 flex items-center justify-center transition-colors"
                                    title="Xóa đánh giá">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <p class="text-gray-700 my-4 leading-relaxed">{{ review.content }}</p>
                    </div>

                    <!-- Hiển thị hình ảnh đánh giá -->
                    <div v-if="review.images && review.images.length > 0" class="mt-4 flex flex-wrap gap-3">
                        <div v-for="image in review.images" :key="image.id"
                            class="relative group overflow-hidden rounded-lg shadow-sm">
                            <img :src="'/storage/' + image.image_path" :alt="'Hình ảnh đánh giá'"
                                class="w-24 h-24 object-cover cursor-pointer transition-transform duration-300 group-hover:scale-110"
                                @click="$emit('openImageModal', '/storage/' + image.image_path)" />
                            <div
                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                                <i
                                    class="bi bi-zoom-in text-white opacity-0 group-hover:opacity-100 text-xl transition-opacity duration-300"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Hiển thị phản hồi của đánh giá -->
                    <div v-if="review.replies && review.replies.length > 0" class="mt-6 border-t border-gray-100 pt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Phản hồi:</h4>
                        <div v-for="reply in review.replies" :key="reply.id" class="bg-gray-50 rounded-lg p-4 mb-3">
                            <div class="flex items-start gap-3">
                                <img :src="reply.user?.avatar ? (reply.user.avatar.startsWith('http') ? reply.user.avatar : '/storage/avatars/' + reply.user.avatar.split('/').pop()) : 'https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg'"
                                    :alt="reply.user?.name"
                                    class="w-8 h-8 rounded-full object-cover border border-gray-200" />
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <div class="font-medium text-gray-800">
                                            {{ reply.user?.username || reply.user?.name }}
                                            <span v-if="reply.is_admin_reply"
                                                class="bg-[#81AACC]/10 text-[#81AACC] text-xs px-2 py-0.5 rounded-full ml-2">Admin</span>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ new Date(reply.created_at).toLocaleDateString() }}
                                        </div>
                                    </div>
                                    <p class="text-gray-700 text-sm">{{ reply.content }}</p>

                                    <div v-if="reply.images && reply.images.length > 0"
                                        class="mt-2 flex flex-wrap gap-2">
                                        <div v-for="image in reply.images" :key="image.id"
                                            class="relative group overflow-hidden rounded-lg shadow-sm">
                                            <img :src="'/storage/' + image.image_path" :alt="'Hình ảnh phản hồi'"
                                                class="w-16 h-16 object-cover cursor-pointer transition-transform duration-300 group-hover:scale-110"
                                                @click="$emit('openImageModal', '/storage/' + image.image_path)" />
                                            <div
                                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                                                <i
                                                    class="bi bi-zoom-in text-white opacity-0 group-hover:opacity-100 text-xl transition-opacity duration-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Pagination -->
            <div v-if="reviewPaginationData && totalReviewPages > 1"
                class="flex justify-between items-center bg-white rounded-lg shadow p-4 mt-6">
                <div class="text-sm text-gray-600">
                    <span v-if="reviewsLoading">Đang tải...</span>
                    <span v-else>Hiển thị {{ reviewPaginationData.from }} - {{ reviewPaginationData.to }} trong tổng số
                        {{ totalReviews }} đánh giá ({{ reviewsPerPage }} đánh giá/trang)</span>
                </div>
                <div class="flex gap-2">
                    <button @click="$emit('handleReviewPageChange', currentReviewPage - 1)"
                        :disabled="currentReviewPage === 1 || reviewsLoading"
                        class="px-3 py-1 border rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                        <i class="bi bi-chevron-left mr-1"></i>Trước
                    </button>
                    <div class="flex gap-1">
                        <button v-for="page in getVisibleReviewPages()" :key="page"
                            @click="$emit('handleReviewPageChange', page)" :disabled="reviewsLoading" :class="[
                                'px-3 py-1 border rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed',
                                page === currentReviewPage
                                    ? 'bg-[#81AACC] text-white border-[#81AACC]'
                                    : 'bg-white text-gray-700 hover:bg-gray-50'
                            ]">
                            {{ page }}
                        </button>
                    </div>
                    <button @click="$emit('handleReviewPageChange', currentReviewPage + 1)"
                        :disabled="currentReviewPage === totalReviewPages || reviewsLoading"
                        class="px-3 py-1 border rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                        Sau<i class="bi bi-chevron-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

// Bỏ useRuntimeConfig, không dùng Nuxt
// const runtimeConfig = useRuntimeConfig()

const props = defineProps({
    reviewStats: {
        type: Object,
        required: true
    },
    showReviewForm: {
        type: Boolean,
        default: false
    },
    isAuthenticated: {
        type: Boolean,
        default: false
    },
    userHasReviewed: {
        type: Boolean,
        default: false
    },
    userReview: {
        type: Object,
        default: null
    },
    reviewForm: {
        type: Object,
        required: true
    },
    editingReviewId: {
        type: [String, Number],
        default: null
    },
    isSubmitting: {
        type: Boolean,
        default: false
    },
    previewImages: {
        type: Array,
        default: () => []
    },
    reviewsLoading: {
        type: Boolean,
        default: false
    },
    reviews: {
        type: Array,
        default: () => []
    },
    reviewPaginationData: {
        type: Object,
        default: null
    },
    totalReviewPages: {
        type: Number,
        default: 1
    },
    totalReviews: {
        type: Number,
        default: 0
    },
    reviewsPerPage: {
        type: Number,
        default: 3
    },
    currentReviewPage: {
        type: Number,
        default: 1
    },
    user: {
        type: Object,
        default: null
    }
})

const emit = defineEmits([
    'update:reviewForm',
    'update:showReviewForm',
    'submitReview',
    'handleImageUpload',
    'removeImage',
    'cancelEdit',
    'editReview',
    'removeReview',
    'openImageModal',
    'handleReviewPageChange'
])

const canModifyReview = (review) => {
    return props.isAuthenticated && props.user && review.user_id === props.user.id
}

const getVisibleReviewPages = () => {
    const pages = []
    const maxVisible = 5
    let start = Math.max(1, props.currentReviewPage - Math.floor(maxVisible / 2))
    let end = Math.min(props.totalReviewPages, start + maxVisible - 1)

    if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1)
    }

    for (let i = start; i <= end; i++) {
        pages.push(i)
    }

    return pages
}
</script>