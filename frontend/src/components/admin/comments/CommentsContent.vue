<template>
    <div class="comments-page">
        <div class="page-header flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 sm:mb-6 gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Đánh giá</h1>
                <p class="text-sm sm:text-base text-gray-600">
                    Quản lý đánh giá sản phẩm - Đánh giá mới nhất hiển thị đầu tiên
                </p>
            </div>
            <div class="flex gap-3">
                <button @click="handleFilterChange"
                    class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                    <i class="fas fa-sync-alt mr-1 sm:mr-2"></i>Tải lại
                </button>
                <button @click="viewNegativeReviews"
                    class="inline-flex items-center px-3 sm:px-4 py-2 bg-red-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
                    <i class="fas fa-exclamation-triangle mr-1 sm:mr-2"></i>Xem đánh giá tiêu cực
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow mb-4 sm:mb-6">
            <div class="p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                <button :class="[
                    'font-semibold pb-2 cursor-pointer text-sm sm:text-base',
                    activeTab === 'reviews'
                        ? 'border-b-2 border-[#3bb77e] text-[#3bb77e]'
                        : 'text-gray-500',
                ]" @click="activeTab = 'reviews'">
                    Danh sách đánh giá
                    <span v-if="filteredReviews.length > 0"
                        class="bg-primary text-white rounded-full px-2 ml-1 text-xs">{{ filteredReviews.length
                        }}</span>
                </button>
                <button :class="[
                    'font-semibold pb-2 cursor-pointer text-sm sm:text-base',
                    activeTab === 'products'
                        ? 'border-b-2 border-[#3bb77e] text-[#3bb77e]'
                        : 'text-gray-500',
                ]" @click="activeTab = 'products'">
                    Sản phẩm đánh giá
                </button>
            </div>

            <!-- Pagination Summary -->
            <div v-if="activeTab === 'reviews' && paginationData"
                class="p-3 sm:p-4 bg-gray-50 border-t border-gray-300">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 text-xs sm:text-sm">
                    <div class="text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Trang {{ currentPage }} / {{ totalPages }} - Tổng
                        {{ totalItems }} đánh giá
                    </div>
                    <div class="text-gray-500">
                        <i class="fas fa-clock mr-1"></i>
                        Đánh giá mới nhất hiển thị đầu tiên
                    </div>
                </div>

                <div v-if="currentFilter.badwords" class="mt-2 text-xs text-blue-600">
                    <i class="fas fa-filter mr-1"></i>
                    <strong>Đang lọc:</strong> Chỉ hiển thị đánh giá có từ ngữ tiêu cực ({{ totalItems }} đánh giá)
                </div>
                <div v-else-if="currentFilter.admin" class="mt-2 text-xs text-green-600">
                    <i class="fas fa-eye mr-1"></i>
                    <strong>Đang hiển thị:</strong> Tất cả đánh giá ({{ totalItems }} đánh giá) - Bao gồm cả đánh giá bị
                    ẩn và chờ duyệt
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>
                <span class="text-red-700">{{ error }}</span>
                <button @click="fetchReviews(currentPage)" class="ml-auto text-red-600 hover:text-red-800">
                    <i class="fas fa-refresh mr-1"></i>Thử lại
                </button>
            </div>
        </div>

        <ProductReviewMenu v-if="activeTab === 'products'" />
        <CommentsList v-else :comments="filteredReviews" :pagination="paginationData" :loading="loading"
            @update-status="handleUpdateStatus" @delete="handleDelete" @add-reply="handleAddReply"
            @update-reply="handleUpdateReply" @page-change="handlePageChange" />

        <!-- Pagination Controls -->
        <div v-if="activeTab === 'reviews' && paginationData && totalPages > 1"
            class="mt-4 sm:mt-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 bg-white rounded-lg shadow p-3 sm:p-4">
            <div class="text-xs sm:text-sm text-gray-600 text-center sm:text-left">
                <span v-if="loading">Đang tải...</span>
                <span v-else>Hiển thị {{ paginationData.from }} - {{ paginationData.to }} trong
                    tổng số {{ totalItems }} đánh giá ({{ perPage }} đánh giá/trang)</span>
            </div>
            <div class="flex gap-1 sm:gap-2 justify-center">
                <button @click="handlePageChange(currentPage - 1)" :disabled="currentPage === 1 || loading"
                    class="px-2 sm:px-3 py-1 border rounded text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                    <i class="fas fa-chevron-left mr-1"></i><span class="hidden sm:inline">Trước</span>
                </button>
                <div class="flex gap-1">
                    <button v-for="page in getVisiblePages()" :key="page" @click="handlePageChange(page)"
                        :disabled="loading" :class="[
                            'px-2 sm:px-3 py-1 border rounded text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed',
                            page === currentPage
                                ? 'bg-primary text-white border-primary'
                                : 'bg-white text-gray-700 hover:bg-gray-50',
                        ]">
                        {{ page }}
                    </button>
                </div>
                <button @click="handlePageChange(currentPage + 1)" :disabled="currentPage === totalPages || loading"
                    class="px-2 sm:px-3 py-1 border rounded text-xs sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50">
                    <span class="hidden sm:inline">Sau</span><i class="fas fa-chevron-right ml-1"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import Cookies from "js-cookie";
import { useAdminReviews } from "../../../composable/useAdminReviews";
import CommentsList from "./CommentsList.vue";
import ProductReviewMenu from "./ProductReviewMenu.vue";

const {
    getAllReviews,
    updateReviewStatus,
    deleteReview,
    addAdminReply,
    getReviewsByCategory,
    getReviewsByBrand,
    updateAdminReply,
} = useAdminReviews();

const reviews = ref([]);
const categories = ref([]);
const brands = ref([]);
const loading = ref(false);
const error = ref(null);
const filterCategory = ref("");
const filterBrand = ref("");
const activeTab = ref("reviews");

const currentPage = ref(1);
const perPage = ref(5);
const totalPages = ref(1);
const totalItems = ref(0);
const paginationData = ref(null);
const currentFilter = ref({});

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL; // thay useRuntimeConfig

const user = ref(null);
const getUserFromCookie = () => {
    const userData = Cookies.get("user");
    if (userData) {
        try {
            user.value = JSON.parse(userData);
        } catch {
            user.value = null;
        }
    }
};

const fetchCategories = async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/api/categories`);
        const data = await response.json();
        categories.value = data;
    } catch (err) {
        console.error("Lỗi khi tải danh mục:", err);
    }
};

const fetchBrands = async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/api/brands`);
        const data = await response.json();
        brands.value = data;
    } catch (err) {
        console.error("Lỗi khi tải thương hiệu:", err);
    }
};

const fetchReviews = async (page = 1, filter = {}) => {
    loading.value = true;
    error.value = null;
    try {
        let data;

        if (filter.badwords === 1) {
            data = await getAllReviews(page, perPage.value, { badwords: 1 });
        } else if (filter.negative === 1) {
            data = await getAllReviews(page, perPage.value, { negative: 1 });
        } else if (filterCategory.value && filterBrand.value) {
            data = await getReviewsByCategory(
                filterCategory.value,
                page,
                perPage.value
            );
            data.data = data.data.filter(
                (review) => review.product && review.product.brand_id == filterBrand.value
            );
        } else if (filterCategory.value) {
            data = await getReviewsByCategory(
                filterCategory.value,
                page,
                perPage.value
            );
        } else if (filterBrand.value) {
            data = await getReviewsByBrand(filterBrand.value, page, perPage.value);
        } else {
            data = await getAllReviews(page, perPage.value, { admin: 1 });
        }

        paginationData.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            per_page: data.per_page,
            total: data.total,
            from: data.from,
            to: data.to,
        };

        currentPage.value = data.current_page;
        totalPages.value = data.last_page;
        totalItems.value = data.total;

        reviews.value = await Promise.all(
            data.data.map(async (review) => {
                if (review.parent_id) return null;

                let productInfo = null;
                try {
                    const productResponse = await fetch(
                        `${API_BASE_URL}/api/products/slug/${review.product_slug}`
                    );
                    const productData = await productResponse.json();
                    productInfo = {
                        id: productData.id,
                        name: productData.name,
                        image:
                            productData.images && productData.images.length > 0
                                ? `${API_BASE_URL}/storage/${productData.images[0].image_path}`
                                : "https://via.placeholder.com/150",
                        category_id: productData.category_id,
                        product_slug: review.product_slug,
                    };
                } catch (err) {
                    console.error("Lỗi khi tải thông tin sản phẩm:", err);
                    productInfo = {
                        id: null,
                        name: "Sản phẩm không xác định",
                        image: "https://via.placeholder.com/150",
                        category_id: null,
                        product_slug: review.product_slug,
                    };
                }

                const adminReply = review.replies?.find(
                    (reply) => reply.is_admin_reply
                );

                return {
                    id: review.id,
                    userName: review.user?.username || "Người dùng ẩn danh",
                    userAvatar:
                        review.user?.avatar ||
                        "https://randomuser.me/api/portraits/men/1.jpg",
                    rating: review.rating,
                    content: review.content,
                    date: new Date(review.created_at).toLocaleDateString("vi-VN"),
                    time: new Date(review.created_at).toLocaleTimeString("vi-VN", {
                        hour: "2-digit",
                        minute: "2-digit",
                    }),
                    status: review.is_hidden
                        ? "rejected"
                        : review.is_approved
                            ? "approved"
                            : "pending",
                    productInfo,
                    reply: adminReply
                        ? {
                            id: adminReply.id,
                            content: adminReply.content,
                            date: new Date(adminReply.created_at).toLocaleDateString("vi-VN"),
                            time: new Date(adminReply.created_at).toLocaleTimeString("vi-VN", {
                                hour: "2-digit",
                                minute: "2-digit",
                            }),
                        }
                        : null,
                    replyText: "",
                    isApproved: review.is_approved,
                    isHidden: review.is_hidden,
                    isEditing: false,
                    editReplyText: "",
                    replies: review.replies,
                    images: review.images || [],
                };
            })
        );

        reviews.value = reviews.value.filter((review) => review !== null);
    } catch (err) {
        console.error("Lỗi khi tải đánh giá:", err);
        error.value = "Không thể tải dữ liệu đánh giá. Vui lòng thử lại sau.";
    } finally {
        loading.value = false;
    }
};

const filteredReviews = computed(() => reviews.value);

const handlePageChange = (pageOrFilter) => {
    if (typeof pageOrFilter === "object") {
        currentFilter.value = pageOrFilter;
        currentPage.value = 1;
        fetchReviews(1, pageOrFilter);
    } else {
        fetchReviews(pageOrFilter, currentFilter.value);
        currentPage.value = pageOrFilter;
    }
};

const handleFilterChange = () => {
    currentPage.value = 1;
    fetchReviews(1);
};

const handleUpdateStatus = async ({ id, status }) => {
    try {
        const review = reviews.value.find((r) => r.id === id);
        if (!review) return;

        await updateReviewStatus(id, status);

        if (status === "approved") {
            review.status = "approved";
            review.isApproved = true;
            review.isHidden = false;
        } else if (status === "rejected") {
            review.status = "rejected";
            review.isApproved = false;
            review.isHidden = true;
        } else {
            review.status = "pending";
            review.isApproved = false;
            review.isHidden = false;
        }
    } catch (err) {
        console.error("Lỗi khi cập nhật trạng thái:", err);
    }
};

const handleDelete = async (id) => {
    if (!confirm("Bạn có chắc chắn muốn xóa đánh giá này?")) return;
    try {
        await deleteReview(id);
        const index = reviews.value.findIndex((r) => r.id === id);
        if (index !== -1) reviews.value.splice(index, 1);
    } catch (err) {
        console.error("Lỗi khi xóa đánh giá:", err);
    }
};

const handleAddReply = async ({ id, content }) => {
    try {
        const review = reviews.value.find((r) => r.id === id);
        if (!review || !review.productInfo) return;

        const replyData = {
            content,
            user_id: user.value?.id,
            product_slug: review.productInfo.product_slug,
            is_admin_reply: true,
            is_approved: true,
            is_hidden: false,
        };

        const response = await addAdminReply(review.id, replyData);
        if (response) {
            review.reply = response;
            review.replyText = "";
        }
    } catch (err) {
        console.error("Lỗi khi thêm phản hồi:", err);
    }
};

const handleUpdateReply = async ({ id, content }) => {
    try {
        const review = reviews.value.find((r) => r.id === id);
        if (!review) return;

        let adminReply = review.reply?.is_admin_reply ? review.reply : null;
        if (!adminReply && review.replies?.length > 0) {
            adminReply = review.replies.find((r) => r.is_admin_reply);
        }

        if (!adminReply) return;

        const response = await updateAdminReply(adminReply.id, content);
        if (response) {
            if (review.reply) review.reply.content = content;
            const replyIndex = review.replies.findIndex((r) => r.is_admin_reply);
            if (replyIndex !== -1) review.replies[replyIndex].content = content;
        }
    } catch (err) {
        console.error("Lỗi khi cập nhật phản hồi:", err);
    }
};

const getVisiblePages = () => {
    const pages = [];
    const maxVisible = 5;
    let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
    let end = Math.min(totalPages.value, start + maxVisible - 1);
    if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1);
    for (let i = start; i <= end; i++) pages.push(i);
    return pages;
};

const viewNegativeReviews = () => {
    currentFilter.value = { admin: 1, badwords: 1 };
    currentPage.value = 1;
    fetchReviews(1, { admin: 1, badwords: 1 });
};

onMounted(() => {
    getUserFromCookie();
    fetchCategories();
    fetchBrands();
    fetchReviews();
});
</script>

<style scoped>
.comments-page {
    padding: 1rem;
}

@media (min-width: 640px) {
    .comments-page {
        padding: 1.5rem;
    }
}

.bg-primary {
    background-color: #3bb77e;
}
</style>