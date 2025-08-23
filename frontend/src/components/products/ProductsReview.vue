<template>
    <div class="space-y-6 sm:space-y-8">
        <!-- Review stats section -->
        <div class="bg-gray-50 rounded-lg p-4 sm:p-6 shadow-sm transition-all hover:shadow-md">
            <div class="flex flex-col lg:flex-row items-center gap-6 lg:gap-8">
                <div class="text-center rounded-lg p-3 sm:p-4 min-w-[120px] sm:min-w-[150px]">
                    <div class="text-4xl sm:text-5xl font-bold text-[#81AACC] mb-2">{{ reviewStats.average }}</div>
                    <div class="text-yellow-400 flex gap-1 justify-center mb-2">
                        <i v-for="n in 5" :key="n"
                            :class="n <= Math.round(reviewStats.average) ? 'bi bi-star-fill' : (n <= reviewStats.average + 0.5 ? 'bi bi-star-half' : 'bi bi-star')"
                            class="text-lg sm:text-xl"></i>
                    </div>
                    <div class="text-xs sm:text-sm text-gray-500 font-medium">{{ reviewStats.total }} đánh giá</div>
                </div>
                <div class="flex-1 w-full">
                    <h3 class="text-base sm:text-lg font-medium mb-3 sm:mb-4 text-center lg:text-left">Phân bối đánh giá
                    </h3>
                    <div v-for="rating in reviewStats.distribution" :key="rating.stars"
                        class="flex items-center gap-2 sm:gap-3 mb-2 sm:mb-3 group">
                        <span class="w-12 sm:w-16 font-medium flex items-center gap-1 text-sm sm:text-base">
                            {{ rating.stars }} <i class="bi bi-star-fill text-yellow-400"></i>
                        </span>
                        <div class="flex-1 h-2 sm:h-3 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-yellow-400 rounded-full transition-all duration-500 group-hover:bg-yellow-500"
                                :style="{ width: rating.percentage + '%' }">
                            </div>
                        </div>
                        <span class="w-12 sm:w-16 text-right font-medium text-sm sm:text-base">{{ rating.percentage
                        }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Form - Show when editing an existing review -->
        <div id="review-form" v-if="showReviewForm || editingReviewId"
            class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-100 mb-6 sm:mb-8 transition-all hover:shadow-md">
            <!-- Debug info -->
            <div v-if="editingReviewId" class="mb-4 p-2 bg-blue-100 text-blue-800 rounded text-sm">
                Đang chỉnh sửa đánh giá ID: {{ editingReviewId }}
            </div>
            <h3 class="text-lg sm:text-xl font-semibold mb-4 sm:mb-6 flex items-center gap-2">
                <i class="bi bi-pencil-square text-[#81AACC]"></i>
                {{ editingReviewId ? 'Chỉnh sửa đánh giá của bạn' : 'Viết đánh giá' }}
            </h3>

            <form @submit.prevent="$emit('submitReview')" class="space-y-4 sm:space-y-6">
                <!-- Rating -->
                <div>
                    <label class="block mb-2 sm:mb-3 font-medium text-gray-700 text-sm sm:text-base">Đánh giá của
                        bạn</label>
                    <div class="flex text-2xl sm:text-3xl text-gray-300 mb-2">
                        <button v-for="star in 5" :key="star" type="button"
                            @click="$emit('update:reviewForm', { ...reviewForm, rating: star })"
                            class="focus:outline-none transition-colors duration-200 hover:text-yellow-400 p-1"
                            :class="star <= reviewForm.rating ? 'text-yellow-400' : ''">
                            <i class="bi bi-star-fill"></i>
                        </button>
                    </div>
                    <div class="text-xs sm:text-sm text-gray-500">
                        {{ ['Chọn đánh giá', 'Rất tệ', 'Tệ', 'Bình thường', 'Tốt', 'Rất tốt'][reviewForm.rating] }}
                    </div>
                </div>

                <!-- Content -->
                <div>
                    <label for="review-content"
                        class="block mb-2 sm:mb-3 font-medium text-gray-700 text-sm sm:text-base">Nội dung đánh
                        giá</label>
                    <textarea id="review-content" :value="reviewForm.content"
                        @input="$emit('update:reviewForm', { ...reviewForm, content: $event.target.value })" rows="4"
                        class="w-full border border-gray-300 rounded-lg p-3 transition-colors focus:border-[#81AACC] focus:ring-2 focus:ring-[#81AACC]/20 focus:outline-none text-sm sm:text-base"
                        placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này" required></textarea>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block mb-2 sm:mb-3 font-medium text-gray-700 text-sm sm:text-base">Hình ảnh (tùy
                        chọn)</label>
                    <div
                        class="border-2 border-dashed border-gray-300 rounded-lg p-3 sm:p-4 text-center transition-colors hover:border-[#81AACC] cursor-pointer relative">
                        <input type="file" @change="handleImageChange" accept="image/*" multiple
                            class="absolute inset-0 opacity-0 cursor-pointer">
                        <i class="bi bi-cloud-arrow-up text-2xl sm:text-3xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500 text-sm sm:text-base">Kéo thả hoặc nhấp để tải lên hình ảnh</p>
                        <p class="text-xs text-gray-400 mt-1">Hỗ trợ JPG, PNG, GIF</p>
                    </div>



                    <!-- Image Previews -->
                    <div v-if="Array.isArray(previewImages) && previewImages.length > 0"
                        class="flex flex-wrap gap-2 sm:gap-3 mt-3 sm:mt-4">
                        <div v-for="(image, index) in previewImages" :key="index"
                            class="relative w-20 h-20 sm:w-24 sm:h-24 group overflow-hidden rounded-lg shadow-sm">
                            <img :src="image.url"
                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">

                            <button type="button" @click="$emit('removeImage', index)"
                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 sm:w-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <i class="bi bi-x text-xs sm:text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-2">
                    <button type="submit"
                        class="bg-[#81AACC] text-white px-4 sm:px-6 py-2 sm:py-3 rounded-md font-medium transition-colors hover:bg-[#6B8BA3] flex items-center justify-center min-w-[120px] sm:min-w-[150px] text-sm sm:text-base"
                        :disabled="isSubmitting">
                        <span v-if="isSubmitting">
                            <i class="bi bi-arrow-repeat animate-spin inline-block mr-2"></i> Đang xử lý...
                        </span>
                        <span v-else>
                            <i class="bi bi-send mr-2"></i> {{ editingReviewId ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}
                        </span>
                    </button>

                    <button type="button" @click="$emit('cancelEdit')"
                        class="bg-gray-200 text-gray-700 px-4 sm:px-6 py-2 sm:py-3 rounded-md font-medium transition-colors hover:bg-gray-300 text-sm sm:text-base">
                        <i class="bi bi-x-lg mr-2"></i> {{ editingReviewId ? 'Hủy' : 'Đóng' }}
                    </button>
                </div>
            </form>
        </div>



        <!-- Edit Review Button for users who have already reviewed -->
        <div v-if="!showReviewForm && !editingReviewId && isAuthenticated && userHasReviewed"
            class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-100 mb-6 sm:mb-8 text-center">
            <i class="bi bi-check-circle text-2xl sm:text-3xl text-green-500 mb-3 block"></i>
            <p class="mb-3 sm:mb-4 text-gray-600 text-sm sm:text-base">Bạn đã đánh giá sản phẩm này rồi</p>
            
            <button @click="handleEditReview"
                class="bg-[#81AACC] text-white px-3 sm:px-4 py-1.5 rounded-md font-medium transition-colors hover:bg-[#6B8BA3] inline-flex items-center gap-2 text-xs sm:text-sm">
                <i class="bi bi-pencil"></i> Chỉnh sửa đánh giá của bạn
            </button>
        </div>

        <!-- Notice for users who haven't reviewed yet -->
        <div v-if="isAuthenticated && !userHasReviewed && !showReviewForm"
            class="bg-blue-50 p-4 sm:p-6 rounded-lg border border-blue-200 mb-6 sm:mb-8 text-center">
            <i class="bi bi-info-circle text-2xl sm:text-3xl text-blue-500 mb-3 block"></i>
            <h4 class="font-semibold text-blue-800 mb-2 text-sm sm:text-base">Bạn chưa đánh giá sản phẩm này</h4>
            <p class="text-blue-700 text-xs sm:text-sm mb-3">
                Để đánh giá sản phẩm, bạn cần mua và nhận hàng thành công trước.
            </p>
            <p class="text-blue-600 text-xs">
                Vui lòng vào trang "Đơn hàng của tôi" để viết đánh giá cho các đơn hàng đã hoàn thành.
            </p>
            <a href="/trang-ca-nhan?tab=orders"
                class="bg-blue-600 text-white px-4 py-2 rounded-md inline-block font-medium transition-colors hover:bg-blue-700 text-sm mt-3">
                <i class="bi bi-box-seam mr-1"></i> Xem đơn hàng của tôi
            </a>
        </div>

        <!-- Review List -->
        <div class="space-y-4 sm:space-y-6">
            <h3 class="text-lg sm:text-xl font-semibold mb-4 sm:mb-6 flex items-center gap-2">
                <i class="bi bi-chat-square-text text-[#81AACC]"></i> Đánh giá từ khách hàng
            </h3>

            <!-- Loading State -->
            <div v-if="reviewsLoading" class="text-center py-8 sm:py-10 bg-gray-50 rounded-lg">
                <div
                    class="inline-block animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-[#81AACC] mb-4">
                </div>
                <p class="text-gray-500 text-sm sm:text-base">Đang tải đánh giá...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="reviews.length === 0" class="text-center py-8 sm:py-10 bg-gray-50 rounded-lg">
                <i class="bi bi-chat-square text-3xl sm:text-4xl text-gray-300 mb-3 block"></i>
                <p class="text-gray-500 text-sm sm:text-base">Chưa có đánh giá nào cho sản phẩm này</p>
            </div>

            <!-- Reviews Content -->
            <div v-else class="space-y-4 sm:space-y-6">
                <div v-for="review in reviews" :key="review.id"
                    class="bg-white rounded-lg p-4 sm:p-6 border border-gray-100 shadow-sm transition-all hover:shadow-md">
                    <div
                        class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 sm:gap-0 mb-3 sm:mb-4">
                        <div class="flex items-start gap-3">
                            <img :src="review.user?.avatar ? (review.user.avatar.startsWith('http') ? review.user.avatar : `${apiBaseUrl}/storage/avatars/` + review.user.avatar.split('/').pop()) : 'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'"
                                :alt="review.user?.name"
                                class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover border-2 border-gray-200 flex-shrink-0" />
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-800 text-sm sm:text-base truncate">{{
                                    review.user?.username || review.user?.name
                                }}</div>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mt-1">
                                    <span class="text-xs sm:text-sm text-gray-500 flex items-center gap-1">
                                        <i class="bi bi-calendar3"></i> {{ new
                                            Date(review.created_at).toLocaleDateString() }}
                                    </span>
                                    <span v-if="user && review.user_id === user.id">
                                        <span v-if="review.is_hidden"
                                            class="bg-red-100 text-red-600 text-xs font-semibold rounded-full px-2 sm:px-3 py-1 ml-0 sm:ml-2 mt-1 sm:mt-0 inline-block">Có
                                            từ ngữ tiêu cực - chờ duyệt</span>
                                        <span v-else-if="!review.is_approved"
                                            class="bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full px-2 sm:px-3 py-1 ml-0 sm:ml-2 mt-1 sm:mt-0 inline-block">Đang
                                            chờ duyệt</span>
                                        <span v-else-if="review.is_approved && !review.is_hidden"
                                            class="bg-green-100 text-green-700 text-xs font-semibold rounded-full px-2 sm:px-3 py-1 ml-0 sm:ml-2 mt-1 sm:mt-0 inline-block">Đã
                                            được hiển thị</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-3">
                            <div class="px-2 sm:px-3 py-1 rounded-full flex items-center gap-1">
                                <div class="text-yellow-400">
                                    <i v-for="n in 5" :key="n"
                                        :class="n <= review.rating ? 'bi bi-star-fill' : 'bi bi-star'"
                                        class="text-xs sm:text-sm"></i>
                                </div>
                            </div>
                            <!-- Nút sửa và xóa đánh giá -->
                            <div v-if="canModifyReview(review)" class="flex gap-1 sm:gap-2">
                                <button @click="$emit('editReview', review)"
                                    class="text-[#81AACC] hover:text-[#6B8BA3] bg-[#81AACC]/10 hover:bg-[#81AACC]/20 rounded-full w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center transition-colors"
                                    title="Chỉnh sửa đánh giá">
                                    <i class="bi bi-pencil text-sm"></i>
                                </button>
                                <button @click="$emit('removeReview', review.id)"
                                    class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 rounded-full w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center transition-colors"
                                    title="Xóa đánh giá">
                                    <i class="bi bi-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <p class="text-gray-700 my-3 sm:my-4 leading-relaxed text-sm sm:text-base">{{ review.content }}
                        </p>
                    </div>

                    <!-- Hiển thị hình ảnh đánh giá -->
                    <div v-if="review.images && review.images.length > 0"
                        class="mt-3 sm:mt-4 flex flex-wrap gap-2 sm:gap-3">

                        <div v-for="image in review.images" :key="`${image.id}-${apiBaseUrl}`"
                            class="relative group overflow-hidden rounded-lg shadow-sm">
                            <img :src="getImageUrlWithTimestamp(image.image_path)" :alt="'Hình ảnh đánh giá'"
                                class="w-20 h-20 sm:w-24 sm:h-24 object-cover cursor-pointer transition-transform duration-300 group-hover:scale-110"
                                @click="$emit('openImageModal', getImageUrlWithTimestamp(image.image_path))"
                                @error="console.error('Image failed to load:', getImageUrlWithTimestamp(image.image_path)); $event.target.src = 'https://via.placeholder.com/100x100?text=Error'"
                                @load="getImageUrlWithTimestamp(image.image_path)" />

                        </div>
                    </div>

                    <!-- Hiển thị phản hồi của đánh giá -->
                    <div v-if="review.replies && review.replies.length > 0"
                        class="mt-4 sm:mt-6 border-t border-gray-100 pt-3 sm:pt-4">
                        <h4 class="text-xs sm:text-sm font-medium text-gray-700 mb-2 sm:mb-3">Phản hồi:</h4>
                        <div v-for="reply in review.replies" :key="reply.id"
                            class="bg-gray-50 rounded-lg p-3 sm:p-4 mb-2 sm:mb-3">
                            <div class="flex items-start gap-2 sm:gap-3">
                                <img :src="reply.user?.avatar ? (reply.user.avatar.startsWith('http') ? reply.user.avatar : `${apiBaseUrl}/storage/avatars/` + reply.user.avatar.split('/').pop()) : 'https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg'"
                                    :alt="reply.user?.name"
                                    class="w-6 h-6 sm:w-8 sm:h-8 rounded-full object-cover border border-gray-200 flex-shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <div
                                        class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 mb-1">
                                        <div class="font-medium text-gray-800 text-xs sm:text-sm truncate">
                                            {{ reply.user?.username || reply.user?.name }}
                                            <span v-if="reply.is_admin_reply"
                                                class="bg-[#81AACC]/10 text-[#81AACC] text-xs px-1 sm:px-2 py-0.5 rounded-full ml-1 sm:ml-2">Admin</span>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ new Date(reply.created_at).toLocaleDateString() }}
                                        </div>
                                    </div>
                                    <p class="text-gray-700 text-xs sm:text-sm">{{ reply.content }}</p>

                                    <div v-if="reply.images && reply.images.length > 0"
                                        class="mt-2 flex flex-wrap gap-1 sm:gap-2">
                                        <div v-for="image in reply.images" :key="image.id"
                                            class="relative group overflow-hidden rounded-lg shadow-sm">
                                            <img :src="getImageUrlWithTimestamp(image.image_path)"
                                                :alt="'Hình ảnh phản hồi'"
                                                class="w-12 h-12 sm:w-16 sm:h-16 object-cover cursor-pointer transition-transform duration-300 group-hover:scale-110"
                                                @click="$emit('openImageModal', getImageUrlWithTimestamp(image.image_path))"
                                                @error="console.error('Reply image failed to load:', getImageUrlWithTimestamp(image.image_path))"
                                                @load="getImageUrlWithTimestamp(image.image_path)" />
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
                class="flex flex-col sm:flex-row justify-between items-center bg-white rounded-lg shadow p-3 sm:p-4 mt-4 sm:mt-6 gap-3 sm:gap-0">
                <div class="text-xs sm:text-sm text-gray-600 text-center sm:text-left">
                    <span v-if="reviewsLoading">Đang tải...</span>
                    <span v-else>Hiển thị {{ reviewPaginationData.from }} - {{ reviewPaginationData.to }} trong tổng số
                        {{ totalReviews }} đánh giá ({{ reviewsPerPage }} đánh giá/trang)</span>
                </div>
                <div class="flex gap-1 sm:gap-2">
                    <button @click="$emit('handleReviewPageChange', currentReviewPage - 1)"
                        :disabled="currentReviewPage === 1 || reviewsLoading"
                        class="px-2 sm:px-3 py-1 border rounded text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                        <i class="bi bi-chevron-left mr-1"></i>Trước
                    </button>
                    <div class="flex gap-1">
                        <button v-for="page in getVisibleReviewPages()" :key="page"
                            @click="$emit('handleReviewPageChange', page)" :disabled="reviewsLoading" :class="[
                                'px-2 sm:px-3 py-1 border rounded text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed',
                                page === currentReviewPage
                                    ? 'bg-[#81AACC] text-white border-[#81AACC]'
                                    : 'bg-white text-gray-700 hover:bg-gray-50'
                            ]">
                            {{ page }}
                        </button>
                    </div>
                    <button @click="$emit('handleReviewPageChange', currentReviewPage + 1)"
                        :disabled="currentReviewPage === totalReviewPages || reviewsLoading"
                        class="px-2 sm:px-3 py-1 border rounded text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                        Sau<i class="bi bi-chevron-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const apiBaseUrl = ref(import.meta.env.VITE_API_BASE_URL)

const getImageUrl = (imagePath) => {
    const url = `${apiBaseUrl.value}/storage/${imagePath}`
    return url
}

const getImageUrlWithTimestamp = (imagePath) => {
    const url = `${apiBaseUrl.value}/storage/${imagePath}?t=${Date.now()}`
    return url
}



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

const handleImageChange = (event) => {

    if (event.target.files && event.target.files.length > 0) {
        Array.from(event.target.files).forEach((file, index) => {
        })
    }
    emit('handleImageUpload', event)
}

const handleEditReview = () => {
    console.log('handleEditReview called, userReview:', props.userReview)
    console.log('userHasReviewed:', props.userHasReviewed)
    console.log('isAuthenticated:', props.isAuthenticated)
    console.log('user:', props.user)
    
    if (props.userReview) {
        console.log('Emitting editReview with:', props.userReview)
        emit('editReview', props.userReview)
    } else {
        console.error('userReview is null or undefined')
        // Fallback: try to find user's review in the reviews array
        const userReview = props.reviews.find(review => 
            props.user && review.user_id === props.user.id
        )
        if (userReview) {
            console.log('Found user review in reviews array:', userReview)
            emit('editReview', userReview)
        } else {
            console.error('No user review found in reviews array either')
        }
    }
}

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