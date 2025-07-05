<template>
    <div class="tw-space-y-8">
        <!-- Review stats section -->
        <div class="tw-bg-gray-50 tw-rounded-lg tw-p-6 tw-shadow-sm tw-transition-all hover:tw-shadow-md">
            <div class="tw-flex tw-flex-col md:tw-flex-row tw-items-center tw-gap-8">
                <div class="tw-text-center tw-rounded-lg tw-p-4 tw-min-w-[150px]">
                    <div class="tw-text-5xl tw-font-bold tw-text-[#81AACC] tw-mb-2">{{ reviewStats.average }}</div>
                    <div class="tw-text-yellow-400 tw-flex tw-gap-1 tw-justify-center tw-mb-2">
                        <i v-for="n in 5" :key="n"
                            :class="n <= Math.round(reviewStats.average) ? 'bi bi-star-fill' : (n <= reviewStats.average + 0.5 ? 'bi bi-star-half' : 'bi bi-star')"
                            class="tw-text-xl"></i>
                    </div>
                    <div class="tw-text-sm tw-text-gray-500 tw-font-medium">{{ reviewStats.total }} đánh giá</div>
                </div>
                <div class="tw-flex-1">
                    <h3 class="tw-text-lg tw-font-medium tw-mb-4 tw-text-center md:tw-text-left">Phân bối đánh giá</h3>
                    <div v-for="rating in reviewStats.distribution" :key="rating.stars"
                        class="tw-flex tw-items-center tw-gap-3 tw-mb-3 tw-group">
                        <span class="tw-w-16 tw-font-medium tw-flex tw-items-center tw-gap-1">
                            {{ rating.stars }} <i class="bi bi-star-fill tw-text-yellow-400"></i>
                        </span>
                        <div class="tw-flex-1 tw-h-3 tw-bg-gray-200 tw-rounded-full tw-overflow-hidden">
                            <div class="tw-h-full tw-bg-yellow-400 tw-rounded-full tw-transition-all tw-duration-500 group-hover:tw-bg-yellow-500"
                                :style="{ width: rating.percentage + '%' }">
                            </div>
                        </div>
                        <span class="tw-w-16 tw-text-right tw-font-medium">{{ rating.percentage }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Form -->
        <div id="review-form" v-if="showReviewForm"
            class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-100 tw-mb-8 tw-transition-all hover:tw-shadow-md">
            <h3 class="tw-text-xl tw-font-semibold tw-mb-6 tw-flex tw-items-center tw-gap-2">
                <i class="bi bi-pencil-square tw-text-[#81AACC]"></i>
                {{ editingReviewId ? 'Chỉnh sửa đánh giá' : 'Viết đánh giá' }}
            </h3>

            <div v-if="!isAuthenticated" class="tw-text-center tw-py-6 tw-bg-gray-50 tw-rounded-lg">
                <i class="bi bi-person-lock tw-text-3xl tw-text-gray-400 tw-mb-2 tw-block"></i>
                <p class="tw-mb-4 tw-text-gray-600">Vui lòng đăng nhập để đánh giá sản phẩm</p>
                <NuxtLink to="/login"
                    class="tw-bg-[#81AACC] tw-text-white tw-px-6 tw-py-2 tw-rounded-md tw-inline-block tw-font-medium tw-transition-colors hover:tw-bg-[#6B8BA3]">
                    <i class="bi bi-box-arrow-in-right tw-mr-1"></i> Đăng nhập
                </NuxtLink>
            </div>

            <form v-else @submit.prevent="$emit('submitReview')" class="tw-space-y-6">
                <!-- Rating -->
                <div>
                    <label class="tw-block tw-mb-3 tw-font-medium tw-text-gray-700">Đánh giá của bạn</label>
                    <div class="tw-flex tw-text-3xl tw-text-gray-300 tw-mb-2">
                        <button v-for="star in 5" :key="star" type="button"
                            @click="$emit('update:reviewForm', { ...reviewForm, rating: star })"
                            class="tw-focus:outline-none tw-transition-colors tw-duration-200 hover:tw-text-yellow-400"
                            :class="star <= reviewForm.rating ? 'tw-text-yellow-400' : ''">
                            <i class="bi bi-star-fill"></i>
                        </button>
                    </div>
                    <div class="tw-text-sm tw-text-gray-500">
                        {{ ['Chọn đánh giá', 'Rất tệ', 'Tệ', 'Bình thường', 'Tốt', 'Rất tốt'][reviewForm.rating] }}
                    </div>
                </div>

                <!-- Content -->
                <div>
                    <label for="review-content" class="tw-block tw-mb-3 tw-font-medium tw-text-gray-700">Nội dung đánh
                        giá</label>
                    <textarea id="review-content" :value="reviewForm.content"
                        @input="$emit('update:reviewForm', { ...reviewForm, content: $event.target.value })" rows="4"
                        class="tw-w-full tw-border tw-border-gray-300 tw-rounded-lg tw-p-3 tw-transition-colors focus:tw-border-[#81AACC] focus:tw-ring-2 focus:tw-ring-[#81AACC]/20 focus:tw-outline-none"
                        placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này" required></textarea>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="tw-block tw-mb-3 tw-font-medium tw-text-gray-700">Hình ảnh (tùy chọn)</label>
                    <div
                        class="tw-border-2 tw-border-dashed tw-border-gray-300 tw-rounded-lg tw-p-4 tw-text-center tw-transition-colors hover:tw-border-[#81AACC] tw-cursor-pointer tw-relative">
                        <input type="file" @change="$emit('handleImageUpload', $event)" accept="image/*" multiple
                            class="tw-absolute tw-inset-0 tw-opacity-0 tw-cursor-pointer">
                        <i class="bi bi-cloud-arrow-up tw-text-3xl tw-text-gray-400 tw-mb-2"></i>
                        <p class="tw-text-gray-500">Kéo thả hoặc nhấp để tải lên hình ảnh</p>
                        <p class="tw-text-xs tw-text-gray-400 tw-mt-1">Hỗ trợ JPG, PNG, GIF</p>
                    </div>

                    <!-- Image Previews -->
                    <div v-if="previewImages.length > 0" class="tw-flex tw-flex-wrap tw-gap-3 tw-mt-4">
                        <div v-for="(image, index) in previewImages" :key="index"
                            class="tw-relative tw-w-24 tw-h-24 tw-group tw-overflow-hidden tw-rounded-lg tw-shadow-sm">
                            <img :src="image.url"
                                class="tw-w-full tw-h-full tw-object-cover tw-transition-transform tw-duration-300 group-hover:tw-scale-110">
                            <div
                                class="tw-absolute tw-inset-0 tw-bg-black tw-bg-opacity-0 group-hover:tw-bg-opacity-30 tw-transition-all tw-duration-300">
                            </div>
                            <button type="button" @click="$emit('removeImage', index)"
                                class="tw-absolute tw-top-1 tw-right-1 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-6 tw-h-6 tw-flex tw-items-center tw-justify-center tw-opacity-0 group-hover:tw-opacity-100 tw-transition-opacity tw-duration-300">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="tw-flex tw-gap-3 tw-pt-2">
                    <button type="submit"
                        class="tw-bg-[#81AACC] tw-text-white tw-px-6 tw-py-3 tw-rounded-md tw-font-medium tw-transition-colors hover:tw-bg-[#6B8BA3] tw-flex tw-items-center tw-justify-center tw-min-w-[150px]"
                        :disabled="isSubmitting">
                        <span v-if="isSubmitting">
                            <i class="bi bi-arrow-repeat tw-animate-spin tw-inline-block tw-mr-2"></i> Đang xử lý...
                        </span>
                        <span v-else>
                            <i class="bi bi-send tw-mr-2"></i> {{ editingReviewId ? 'Cập nhật đánh giá' : 'Gửi đánh giá'
                            }}
                        </span>
                    </button>

                    <button type="button" @click="$emit('cancelEdit')"
                        class="tw-bg-gray-200 tw-text-gray-700 tw-px-6 tw-py-3 tw-rounded-md tw-font-medium tw-transition-colors hover:tw-bg-gray-300">
                        <i class="bi bi-x-lg tw-mr-2"></i> {{ editingReviewId ? 'Hủy' : 'Đóng' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Review Form Toggle Button -->
        <div v-if="!showReviewForm && isAuthenticated && !userHasReviewed"
            class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-100 tw-mb-8 tw-text-center">
            <i class="bi bi-chat-square-text tw-text-3xl tw-text-gray-400 tw-mb-3 tw-block"></i>
            <p class="tw-mb-4 tw-text-gray-600">Bạn chưa đánh giá sản phẩm này</p>
            <button @click="$emit('update:showReviewForm', true)"
                class="tw-bg-[#81AACC] tw-text-white tw-px-4 tw-py-1.5 tw-rounded-md tw-font-medium tw-transition-colors hover:tw-bg-[#6B8BA3] tw-inline-flex tw-items-center tw-gap-2 tw-text-sm">
                <i class="bi bi-pencil-square"></i> Viết đánh giá
            </button>
        </div>

        <!-- Edit Review Button for users who have already reviewed -->
        <div v-if="!showReviewForm && isAuthenticated && userHasReviewed"
            class="tw-bg-white tw-p-6 tw-rounded-lg tw-shadow-sm tw-border tw-border-gray-100 tw-mb-8 tw-text-center">
            <i class="bi bi-check-circle tw-text-3xl tw-text-green-500 tw-mb-3 tw-block"></i>
            <p class="tw-mb-4 tw-text-gray-600">Bạn đã đánh giá sản phẩm này rồi</p>
            <button @click="$emit('editReview', userReview)"
                class="tw-bg-[#81AACC] tw-text-white tw-px-4 tw-py-1.5 tw-rounded-md tw-font-medium tw-transition-colors hover:tw-bg-[#6B8BA3] tw-inline-flex tw-items-center tw-gap-2 tw-text-sm">
                <i class="bi bi-pencil"></i> Chỉnh sửa đánh giá của bạn
            </button>
        </div>

        <!-- Review List -->
        <div class="tw-space-y-6">
            <h3 class="tw-text-xl tw-font-semibold tw-mb-6 tw-flex tw-items-center tw-gap-2">
                <i class="bi bi-chat-square-text tw-text-[#81AACC]"></i> Đánh giá từ khách hàng
            </h3>

            <!-- Loading State -->
            <div v-if="reviewsLoading" class="tw-text-center tw-py-10 tw-bg-gray-50 tw-rounded-lg">
                <div
                    class="tw-inline-block tw-animate-spin tw-rounded-full tw-h-8 tw-w-8 tw-border-b-2 tw-border-[#81AACC] tw-mb-4">
                </div>
                <p class="tw-text-gray-500">Đang tải đánh giá...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="reviews.length === 0" class="tw-text-center tw-py-10 tw-bg-gray-50 tw-rounded-lg">
                <i class="bi bi-chat-square tw-text-4xl tw-text-gray-300 tw-mb-3 tw-block"></i>
                <p class="tw-text-gray-500">Chưa có đánh giá nào cho sản phẩm này</p>
            </div>

            <!-- Reviews Content -->
            <div v-else class="tw-space-y-6">
                <div v-for="review in reviews" :key="review.id"
                    class="tw-bg-white tw-rounded-lg tw-p-6 tw-border tw-border-gray-100 tw-shadow-sm tw-transition-all hover:tw-shadow-md">
                    <div class="tw-flex tw-justify-between tw-mb-4">
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <img :src="review.user?.avatar ? (review.user.avatar.startsWith('http') ? review.user.avatar : runtimeConfig.public.apiBaseUrl +
                                '/storage/avatars/' + review.user.avatar.split('/').pop()) : 'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'"
                                :alt="review.user?.name"
                                class="tw-w-12 tw-h-12 tw-rounded-full tw-object-cover tw-border-2 tw-border-gray-200" />
                            <div>
                                <div class="tw-font-semibold tw-text-gray-800">{{ review.user?.username ||
                                    review.user?.name }}
                                </div>
                                <div class="tw-text-sm tw-text-gray-500 tw-flex tw-items-center tw-gap-1">
                                    <i class="bi bi-calendar3"></i> {{ new Date(review.created_at).toLocaleDateString()
                                    }}
                                </div>
                            </div>
                        </div>
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <div class="tw-px-3 tw-py-1 tw-rounded-full tw-flex tw-items-center tw-gap-1">
                                <div class="tw-text-yellow-400">
                                    <i v-for="n in 5" :key="n"
                                        :class="n <= review.rating ? 'bi bi-star-fill' : 'bi bi-star'"
                                        class="tw-text-sm"></i>
                                </div>
                            </div>
                            <!-- Nút sửa và xóa đánh giá -->
                            <div v-if="canModifyReview(review)" class="tw-flex tw-gap-2">
                                <button @click="$emit('editReview', review)"
                                    class="tw-text-[#81AACC] hover:tw-text-[#6B8BA3] tw-bg-[#81AACC]/10 hover:tw-bg-[#81AACC]/20 tw-rounded-full tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-transition-colors"
                                    title="Chỉnh sửa đánh giá">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button @click="$emit('removeReview', review.id)"
                                    class="tw-text-red-600 hover:tw-text-red-800 tw-bg-red-50 hover:tw-bg-red-100 tw-rounded-full tw-w-8 tw-h-8 tw-flex tw-items-center tw-justify-center tw-transition-colors"
                                    title="Xóa đánh giá">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="tw-text-gray-700 tw-my-4 tw-leading-relaxed">{{ review.content }}</p>

                    <!-- Hiển thị hình ảnh đánh giá -->
                    <div v-if="review.images && review.images.length > 0" class="tw-mt-4 tw-flex tw-flex-wrap tw-gap-3">
                        <div v-for="image in review.images" :key="image.id"
                            class="tw-relative tw-group tw-overflow-hidden tw-rounded-lg tw-shadow-sm">
                            <img :src="runtimeConfig.public.apiBaseUrl + '/storage/' + image.image_path"
                                :alt="'Hình ảnh đánh giá'"
                                class="tw-w-24 tw-h-24 tw-object-cover tw-cursor-pointer tw-transition-transform tw-duration-300 group-hover:tw-scale-110"
                                @click="$emit('openImageModal', runtimeConfig.public.apiBaseUrl + '/storage/' + image.image_path)" />
                            <div
                                class="tw-absolute tw-inset-0 tw-bg-black tw-bg-opacity-0 group-hover:tw-bg-opacity-20 tw-transition-all tw-duration-300 tw-flex tw-items-center tw-justify-center">
                                <i
                                    class="bi bi-zoom-in tw-text-white tw-opacity-0 group-hover:tw-opacity-100 tw-text-xl tw-transition-opacity tw-duration-300"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Hiển thị phản hồi của đánh giá -->
                    <div v-if="review.replies && review.replies.length > 0"
                        class="tw-mt-6 tw-border-t tw-border-gray-100 tw-pt-4">
                        <h4 class="tw-text-sm tw-font-medium tw-text-gray-700 tw-mb-3">Phản hồi:</h4>
                        <div v-for="reply in review.replies" :key="reply.id"
                            class="tw-bg-gray-50 tw-rounded-lg tw-p-4 tw-mb-3">
                            <div class="tw-flex tw-items-start tw-gap-3">
                                <img :src="reply.user?.avatar ? (reply.user.avatar.startsWith('http') ? reply.user.avatar : runtimeConfig.public.apiBaseUrl + '/storage/avatars/' + reply.user.avatar.split('/').pop()) : 'https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg'"
                                    :alt="reply.user?.name"
                                    class="tw-w-8 tw-h-8 tw-rounded-full tw-object-cover tw-border tw-border-gray-200" />
                                <div class="tw-flex-1">
                                    <div class="tw-flex tw-justify-between tw-items-center tw-mb-1">
                                        <div class="tw-font-medium tw-text-gray-800">
                                            {{ reply.user?.username || reply.user?.name }}
                                            <span v-if="reply.is_admin_reply"
                                                class="tw-bg-[#81AACC]/10 tw-text-[#81AACC] tw-text-xs tw-px-2 tw-py-0.5 tw-rounded-full tw-ml-2">Admin</span>
                                        </div>
                                        <div class="tw-text-xs tw-text-gray-500">
                                            {{ new Date(reply.created_at).toLocaleDateString() }}
                                        </div>
                                    </div>
                                    <p class="tw-text-gray-700 tw-text-sm">{{ reply.content }}</p>

                                    <div v-if="reply.images && reply.images.length > 0"
                                        class="tw-mt-2 tw-flex tw-flex-wrap tw-gap-2">
                                        <div v-for="image in reply.images" :key="image.id"
                                            class="tw-relative tw-group tw-overflow-hidden tw-rounded-lg tw-shadow-sm">
                                            <img :src="runtimeConfig.public.apiBaseUrl + '/storage/' + image.image_path"
                                                :alt="'Hình ảnh phản hồi'"
                                                class="tw-w-16 tw-h-16 tw-object-cover tw-cursor-pointer tw-transition-transform tw-duration-300 group-hover:tw-scale-110"
                                                @click="$emit('openImageModal', runtimeConfig.public.apiBaseUrl + '/storage/' + image.image_path)" />
                                            <div
                                                class="tw-absolute tw-inset-0 tw-bg-black tw-bg-opacity-0 group-hover:tw-bg-opacity-20 tw-transition-all tw-duration-300 tw-flex tw-items-center tw-justify-center">
                                                <i
                                                    class="bi bi-zoom-in tw-text-white tw-opacity-0 group-hover:tw-opacity-100 tw-text-xl tw-transition-opacity tw-duration-300"></i>
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
                class="tw-flex tw-justify-between tw-items-center tw-bg-white tw-rounded-lg tw-shadow tw-p-4 tw-mt-6">
                <div class="tw-text-sm tw-text-gray-600">
                    <span v-if="reviewsLoading">Đang tải...</span>
                    <span v-else>Hiển thị {{ reviewPaginationData.from }} - {{ reviewPaginationData.to }} trong tổng số
                        {{
                        totalReviews }} đánh giá ({{ reviewsPerPage }} đánh giá/trang)</span>
                </div>
                <div class="tw-flex tw-gap-2">
                    <button @click="$emit('handleReviewPageChange', currentReviewPage - 1)"
                        :disabled="currentReviewPage === 1 || reviewsLoading"
                        class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm disabled:tw-opacity-50 disabled:tw-cursor-not-allowed hover:tw-bg-gray-50">
                        <i class="bi bi-chevron-left tw-mr-1"></i>Trước
                    </button>
                    <div class="tw-flex tw-gap-1">
                        <button v-for="page in getVisibleReviewPages()" :key="page"
                            @click="$emit('handleReviewPageChange', page)" :disabled="reviewsLoading" :class="[
                                'tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm disabled:tw-opacity-50 disabled:tw-cursor-not-allowed',
                                page === currentReviewPage
                                    ? 'tw-bg-[#81AACC] tw-text-white tw-border-[#81AACC]'
                                    : 'tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50'
                            ]">
                            {{ page }}
                        </button>
                    </div>
                    <button @click="$emit('handleReviewPageChange', currentReviewPage + 1)"
                        :disabled="currentReviewPage === totalReviewPages || reviewsLoading"
                        class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm disabled:tw-opacity-50 disabled:tw-cursor-not-allowed hover:tw-bg-gray-50">
                        Sau<i class="bi bi-chevron-right tw-ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const runtimeConfig = useRuntimeConfig()

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