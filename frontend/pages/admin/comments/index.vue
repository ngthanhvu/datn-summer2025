<template>
  <div class="comments-page">
    <div class="page-header tw-flex tw-justify-between tw-items-center tw-mb-6">
      <div>
        <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Đánh giá</h1>
        <p class="tw-text-gray-600">
          Quản lý đánh giá sản phẩm - Đánh giá mới nhất hiển thị đầu tiên
        </p>
      </div>
      <div class="tw-flex tw-gap-3">
        <button @click="handleFilterChange"
          class="tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-bg-gray-600 tw-text-white tw-text-sm tw-font-medium tw-rounded-lg hover:tw-bg-gray-700 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-gray-500 focus:tw-ring-offset-2 tw-transition-colors tw-duration-200">
          <i class="fas fa-sync-alt tw-mr-2"></i>Tải lại
        </button>
      </div>
    </div>

    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-mb-6">
      <div class="tw-p-4 tw-border-b tw-flex tw-items-center tw-gap-4">
        <button :class="[
          'tw-font-semibold tw-pb-2',
          activeTab === 'reviews'
            ? 'tw-border-b-2 tw-border-primary tw-text-primary'
            : 'tw-text-gray-500',
        ]" @click="activeTab = 'reviews'">
          Danh sách đánh giá
          <span v-if="filteredReviews.length > 0"
            class="tw-bg-primary tw-text-white tw-rounded-full tw-px-2 tw-ml-1 tw-text-xs">{{ filteredReviews.length
            }}</span>
        </button>
        <button :class="[
          'tw-font-semibold tw-pb-2',
          activeTab === 'products'
            ? 'tw-border-b-2 tw-border-primary tw-text-primary'
            : 'tw-text-gray-500',
        ]" @click="activeTab = 'products'">
          Sản phẩm đánh giá
        </button>
      </div>

      <!-- Pagination Summary -->
      <div v-if="activeTab === 'reviews' && paginationData" class="tw-p-4 tw-bg-gray-50 tw-border-t">
        <div class="tw-flex tw-justify-between tw-items-center tw-text-sm">
          <div class="tw-text-gray-600">
            <i class="fas fa-info-circle tw-mr-1"></i>
            Trang {{ currentPage }} / {{ totalPages }} - Tổng
            {{ totalItems }} đánh giá
          </div>
          <div class="tw-text-gray-500">
            <i class="fas fa-clock tw-mr-1"></i>
            Đánh giá mới nhất hiển thị đầu tiên
          </div>
        </div>
        <div class="tw-mt-2 tw-text-xs tw-text-red-500">
          <i class="fas fa-exclamation-triangle tw-mr-1"></i>
          <strong>Lưu ý:</strong> Đánh giá chứa từ khóa tiêu cực sẽ tự động bị ẩn (rejected).
          Đánh giá chưa được duyệt sẽ hiển thị trạng thái "pending".
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="tw-mt-6 tw-bg-red-50 tw-border tw-border-red-200 tw-rounded-lg tw-p-4">
      <div class="tw-flex tw-items-center">
        <i class="fas fa-exclamation-triangle tw-text-red-400 tw-mr-2"></i>
        <span class="tw-text-red-700">{{ error }}</span>
        <button @click="fetchReviews(currentPage)" class="tw-ml-auto tw-text-red-600 hover:tw-text-red-800">
          <i class="fas fa-refresh tw-mr-1"></i>Thử lại
        </button>
      </div>
    </div>

    <ProductReviewMenu v-if="activeTab === 'products'" />
    <CommentsList v-else :comments="filteredReviews" :pagination="paginationData" :loading="loading"
      @update-status="handleUpdateStatus" @delete="handleDelete" @add-reply="handleAddReply"
      @update-reply="handleUpdateReply" @page-change="handlePageChange" />

    <!-- Pagination Controls -->
    <div v-if="activeTab === 'reviews' && paginationData && totalPages > 1"
      class="tw-mt-6 tw-flex tw-justify-between tw-items-center tw-bg-white tw-rounded-lg tw-shadow tw-p-4">
      <div class="tw-text-sm tw-text-gray-600">
        <span v-if="loading">Đang tải...</span>
        <span v-else>Hiển thị {{ paginationData.from }} - {{ paginationData.to }} trong
          tổng số {{ totalItems }} đánh giá ({{ perPage }} đánh giá/trang)</span>
      </div>
      <div class="tw-flex tw-gap-2">
        <button @click="handlePageChange(currentPage - 1)" :disabled="currentPage === 1 || loading"
          class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm disabled:tw-opacity-50 disabled:tw-cursor-not-allowed hover:tw-bg-gray-50">
          <i class="fas fa-chevron-left tw-mr-1"></i>Trước
        </button>
        <div class="tw-flex tw-gap-1">
          <button v-for="page in getVisiblePages()" :key="page" @click="handlePageChange(page)" :disabled="loading"
            :class="[
              'tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm disabled:tw-opacity-50 disabled:tw-cursor-not-allowed',
              page === currentPage
                ? 'tw-bg-primary tw-text-white tw-border-primary'
                : 'tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50',
            ]">
            {{ page }}
          </button>
        </div>
        <button @click="handlePageChange(currentPage + 1)" :disabled="currentPage === totalPages || loading"
          class="tw-px-3 tw-py-1 tw-border tw-rounded tw-text-sm disabled:tw-opacity-50 disabled:tw-cursor-not-allowed hover:tw-bg-gray-50">
          Sau<i class="fas fa-chevron-right tw-ml-1"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
useHead({
  title: "Quản lý đánh giá",
  meta: [{ name: "description", content: "Quản lý đánh giá sản phẩm của bạn" }],
});
definePageMeta({
  layout: "admin",
  middleware: "admin",
});

import { ref, computed, onMounted } from "vue";
import { useAuth } from "~/composables/useAuth";
import { useAdminReviews } from "~/composables/useAdminReviews";
import CommentsList from "~/components/admin/comments/CommentsList.vue";
import ProductReviewMenu from "~/components/admin/comments/ProductReviewMenu.vue";

const { getUser, getToken, user } = useAuth();

const {
  getAllReviews,
  updateReviewStatus,
  deleteReview,
  addAdminReply,
  getReviewsByCategory,
  getReviewsByBrand,
  updateAdminReply,
} = useAdminReviews();
const notyf = useNuxtApp().$notyf;

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

const fetchCategories = async () => {
  try {
    const response = await fetch(
      `${useRuntimeConfig().public.apiBaseUrl}/api/categories`
    );
    const data = await response.json();
    categories.value = data;
  } catch (err) {
    console.error("Lỗi khi tải danh mục:", err);
  }
};

const fetchBrands = async () => {
  try {
    const response = await fetch(
      `${useRuntimeConfig().public.apiBaseUrl}/api/brands`
    );
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
      data.data = data.data.filter((review) => {
        return review.product && review.product.brand_id == filterBrand.value;
      });
    } else if (filterCategory.value) {
      data = await getReviewsByCategory(
        filterCategory.value,
        page,
        perPage.value
      );
    } else if (filterBrand.value) {
      data = await getReviewsByBrand(filterBrand.value, page, perPage.value);
    } else {
      data = await getAllReviews(page, perPage.value);
    }

    // Store pagination data
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
            `${useRuntimeConfig().public.apiBaseUrl}/api/products/slug/${review.product_slug
            }`
          );
          const productData = await productResponse.json();
          productInfo = {
            id: productData.id,
            name: productData.name,
            image:
              productData.images && productData.images.length > 0
                ? `${useRuntimeConfig().public.apiBaseUrl}/storage/${productData.images[0].image_path
                }`
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
              date: new Date(adminReply.created_at).toLocaleDateString(
                "vi-VN"
              ),
              time: new Date(adminReply.created_at).toLocaleTimeString(
                "vi-VN",
                { hour: "2-digit", minute: "2-digit" }
              ),
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

const filteredReviews = computed(() => {
  return reviews.value;
});

const handlePageChange = (pageOrFilter) => {
  if (typeof pageOrFilter === 'object') {
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

    // Cập nhật trạng thái dựa trên logic mới
    if (status === "approved") {
      review.status = "approved";
      review.isApproved = true;
      review.isHidden = false;
    } else if (status === "rejected") {
      review.status = "rejected";
      review.isApproved = false;
      review.isHidden = true;
    } else if (status === "pending") {
      review.status = "pending";
      review.isApproved = false;
      review.isHidden = false;
    }

    notyf.success(`Đã cập nhật trạng thái đánh giá thành công`);
  } catch (err) {
    console.error("Lỗi khi cập nhật trạng thái:", err);
    notyf.error("Không thể cập nhật trạng thái đánh giá. Vui lòng thử lại.");
  }
};

const handleDelete = async (id) => {
  if (!confirm("Bạn có chắc chắn muốn xóa đánh giá này?")) return;

  try {
    await deleteReview(id);

    const index = reviews.value.findIndex((r) => r.id === id);
    if (index !== -1) {
      reviews.value.splice(index, 1);
    }
  } catch (err) {
    console.error("Lỗi khi xóa đánh giá:", err);
    notyf.error("Không thể xóa đánh giá. Vui lòng thử lại.");
  }
};

const handleAddReply = async ({ id, content }) => {
  try {
    const review = reviews.value.find((r) => r.id === id);
    if (!review) {
      console.error("Không tìm thấy review với id:", id);
      notyf.error("Không tìm thấy đánh giá. Vui lòng làm mới trang.");
      return;
    }

    if (!review.productInfo) {
      console.error("Thiếu thông tin sản phẩm cho review:", id);
      notyf.error("Thiếu thông tin sản phẩm. Vui lòng làm mới trang.");
      return;
    }

    const productSlug =
      review.product_slug ||
      (review.productInfo && review.productInfo.product_slug);

    const replyData = {
      content: content,
      user_id: user.value.id,
      product_slug: productSlug,
      is_admin_reply: true,
      is_approved: true,
      is_hidden: false,
    };

    console.log("Gửi request với dữ liệu:", replyData);

    const response = await addAdminReply(review.id, replyData);

    if (response) {
      review.reply = response;
      const commentIndex = reviews.value.findIndex((r) => r.id === id);
      if (commentIndex !== -1) {
        reviews.value[commentIndex].replyText = "";
      }
    }
  } catch (err) {
    console.error("Lỗi khi thêm phản hồi:", err);
    notyf.error("Không thể thêm phản hồi. Vui lòng thử lại.");
  }
};

async function handleUpdateReply({ id, content }) {
  try {
    const review = reviews.value.find((r) => r.id === id);
    if (!review) {
      console.error("Không tìm thấy review với id:", id);
      notyf.error("Không thể cập nhật phản hồi. Review không tồn tại.");
      return;
    }

    let adminReply =
      review.reply && review.reply.is_admin_reply ? review.reply : null;

    if (!adminReply && review.replies && review.replies.length > 0) {
      adminReply = review.replies.find((r) => r.is_admin_reply);
    }

    if (!adminReply) {
      console.error("Không tìm thấy admin reply cho review:", id);
      notyf.error("Không thể cập nhật phản hồi. Admin reply không tồn tại.");
      return;
    }

    if (!review.productInfo || !review.productInfo.product_slug) {
      console.error("Review không có product_slug:", review);
      notyf.error("Không thể cập nhật phản hồi. Thiếu thông tin sản phẩm.");
      return;
    }

    await getUser();
    if (!user.value || !user.value.id) {
      console.error("Không có thông tin user");
      notyf.error("Không thể cập nhật phản hồi. Vui lòng đăng nhập lại.");
      return;
    }

    console.log("Gửi request với dữ liệu:", {
      replyId: adminReply.id,
      content,
    });

    const response = await updateAdminReply(adminReply.id, content);

    if (response) {
      if (review.reply) {
        review.reply.content = content;
      }

      if (review.replies) {
        const replyIndex = review.replies.findIndex((r) => r.is_admin_reply);
        if (replyIndex !== -1) {
          review.replies[replyIndex].content = content;
        }
      }
    }
  } catch (err) {
    console.error("Lỗi khi cập nhật phản hồi:", err);
    notyf.error("Không thể cập nhật phản hồi. Vui lòng thử lại.");
  }
}

const getVisiblePages = () => {
  const pages = [];
  const maxVisible = 5;
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2));
  let end = Math.min(totalPages.value, start + maxVisible - 1);

  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1);
  }

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
};

onMounted(() => {
  fetchCategories();
  fetchBrands();
  fetchReviews();
});
</script>

<style scoped>
.comments-page {
  padding: 1.5rem;
}

.tw-bg-primary {
  background-color: #3bb77e;
}
</style>