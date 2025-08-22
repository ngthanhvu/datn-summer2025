<template>
    <div class="bg-white rounded-lg shadow p-3 sm:p-4">
        <!-- Filter Row -->
        <div class="p-3 sm:p-4">
            <!-- Mobile: Stack filters vertically -->
            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                <!-- Row 1: Status and Rating filters -->
                <div class="flex gap-2 flex-wrap">
                    <select v-model="filterStatus"
                        class="border border-gray-300 rounded px-2 sm:px-3 py-1 text-xs sm:text-sm flex-1 sm:flex-none">
                        <option value="">Trạng thái</option>
                        <option value="pending">Chờ duyệt</option>
                        <option value="approved">Hiển thị</option>
                        <option value="rejected">Vi phạm</option>
                    </select>
                    <select v-model="filterRating"
                        class="border border-gray-300 rounded px-2 sm:px-3 py-1 text-xs sm:text-sm flex-1 sm:flex-none">
                        <option value="">Điểm đánh giá</option>
                        <option v-for="n in 5" :key="n" :value="n">{{ n }} sao</option>
                    </select>
                </div>

                <!-- Row 2: Image and Unread filters -->
                <div class="flex gap-2 flex-wrap">
                    <select v-model="filterHasImage"
                        class="border border-gray-300 rounded px-2 sm:px-3 py-1 text-xs sm:text-sm flex-1 sm:flex-none">
                        <option value="">Có hình ảnh</option>
                        <option value="yes">Có</option>
                        <option value="no">Không</option>
                    </select>
                    <select v-model="filterUnread"
                        class="border border-gray-300 rounded px-2 sm:px-3 py-1 text-xs sm:text-sm flex-1 sm:flex-none">
                        <option value="">Chưa đọc</option>
                        <option value="yes">Đã đọc</option>
                    </select>
                    <select v-model="filterBadwords"
                        class="border border-gray-300 rounded px-2 sm:px-3 py-1 text-xs sm:text-sm flex-1 sm:flex-none">
                        <option value="">Tiêu cực</option>
                        <option value="1">Chỉ tiêu cực</option>
                    </select>
                    <select v-model="filterHidden"
                        class="border border-gray-300 rounded px-2 sm:px-3 py-1 text-xs sm:text-sm flex-1 sm:flex-none">
                        <option value="">Trạng thái ẩn</option>
                        <option value="hidden">Đang bị ẩn</option>
                        <option value="visible">Đang hiển thị</option>
                    </select>
                    <select v-model="filterApproval"
                        class="border border-gray-300 rounded px-2 sm:px-3 py-1 text-xs sm:text-sm flex-1 sm:flex-none">
                        <option value="">Phê duyệt</option>
                        <option value="pending">Chờ duyệt</option>
                        <option value="approved">Đã duyệt</option>
                        <option value="rejected">Bị từ chối</option>
                    </select>
                </div>

                <!-- Row 3: Search input -->
                <div class="w-full sm:w-64">
                    <input v-model="searchQuery" type="text" placeholder="Nhập từ khóa tìm kiếm ..."
                        class="w-full border border-gray-300 rounded px-2 sm:px-3 py-1 text-xs sm:text-sm focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100" />
                </div>
            </div>
        </div>
        <!-- Mobile Card Layout (hidden on desktop) -->
        <div class="block sm:hidden">
            <!-- Empty State -->
            <div v-if="!loading && filteredComments.length === 0" class="p-8 text-center">
                <i class="fas fa-comments text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-600">Không có đánh giá nào</p>
            </div>

            <!-- Loading Cards -->
            <div v-else-if="loading" class="space-y-4">
                <div v-for="n in 3" :key="n" class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="animate-pulse">
                        <div class="flex justify-between items-start mb-3">
                            <div class="bg-gray-200 h-4 rounded w-20"></div>
                            <div class="bg-gray-200 h-6 rounded w-16"></div>
                        </div>
                        <div class="bg-gray-200 h-4 rounded w-full mb-2"></div>
                        <div class="bg-gray-200 h-4 rounded w-3/4 mb-3"></div>
                        <div class="flex justify-between items-center">
                            <div class="bg-gray-200 h-4 rounded w-24"></div>
                            <div class="bg-gray-200 h-8 rounded w-20"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comment Cards -->
            <div v-else class="space-y-4">
                <div v-for="comment in filteredComments" :key="comment.id" :class="['bg-white border border-gray-200 rounded-lg p-4',
                    comment.status === 'rejected' ? 'border-red-200 bg-red-50' :
                        comment.status === 'approved' ? 'border-blue-200 bg-blue-50' : '']">

                    <!-- Header: Rating and Status -->
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-2">
                            <span v-html="renderStars(comment.rating)" class="text-sm"></span>
                            <span class="text-xs text-gray-500">{{ comment.rating }}/5</span>
                        </div>
                        <span :class="[
                            'px-2 py-1 rounded-full text-xs font-medium',
                            comment.status === 'approved' ? 'bg-green-100 text-green-800' :
                                comment.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                    'bg-yellow-100 text-yellow-800'
                        ]">
                            {{ comment.status === 'approved' ? 'Hiển thị' :
                                comment.status === 'rejected' ? 'Vi phạm' : 'Chờ duyệt' }}
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="mb-3">
                        <p class="text-sm text-gray-900 mb-2">{{ comment.content }}</p>

                        <!-- Images -->
                        <div v-if="comment.images && comment.images.length" class="mb-2">
                            <div class="text-xs mb-1 font-semibold text-gray-600">Hình ảnh đánh giá:</div>
                            <div class="flex gap-2 flex-wrap">
                                <img v-for="img in comment.images" :key="img.id" :src="getImageUrl(img.image_path)"
                                    class="w-12 h-12 object-cover rounded border" alt="review image" />
                            </div>
                        </div>

                        <!-- User and Product Info -->
                        <div class="text-xs text-gray-500">
                            <div class="mb-1">
                                <i class="fas fa-user mr-1"></i>
                                <span class="font-semibold">{{ comment.userEmail || comment.userName }}</span>
                            </div>
                            <div class="mb-1">
                                <i class="fas fa-box mr-1"></i>
                                <span class="text-blue-600">{{ comment.productInfo?.name }}</span>
                            </div>
                            <div>
                                <i class="fas fa-clock mr-1"></i>
                                {{ comment.date }} {{ comment.time }}
                            </div>
                        </div>
                    </div>

                    <!-- Admin Reply -->
                    <div v-if="comment.reply" class="mb-3 ml-2 p-2 bg-gray-100 rounded border-l-2 border-blue-400">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas fa-user-shield text-blue-600 text-xs"></i>
                            <span class="text-xs font-semibold text-blue-600">Phản hồi của Admin</span>
                            <span class="text-xs text-gray-500">{{ comment.reply.date }} {{ comment.reply.time }}</span>
                        </div>
                        <p class="text-xs text-gray-700">{{ comment.reply.content }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-200">
                        <button @click="updateStatus(comment.id, 'approved')" :disabled="comment.status === 'approved'"
                            class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-check mr-1"></i>Duyệt
                        </button>
                        <button @click="updateStatus(comment.id, 'rejected')" :disabled="comment.status === 'rejected'"
                            class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-times mr-1"></i>Từ chối
                        </button>
                        <button @click="deleteComment(comment.id)"
                            class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">
                            <i class="fas fa-trash mr-1"></i>Xóa
                        </button>
                        <button @click="toggleReplyForm(comment)"
                            class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                            <i class="fas fa-reply mr-1"></i>Phản hồi
                        </button>
                    </div>

                    <!-- Reply Form -->
                    <div v-if="comment.showReplyForm" class="mt-3 p-3 bg-gray-50 rounded border">
                        <textarea v-model="comment.replyText" placeholder="Nhập phản hồi của bạn..."
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            rows="3"></textarea>
                        <div class="flex gap-2 mt-2">
                            <button @click="submitReply(comment)" :disabled="!comment.replyText.trim()"
                                class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-paper-plane mr-1"></i>Gửi
                            </button>
                            <button @click="comment.showReplyForm = false"
                                class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">
                                <i class="fas fa-times mr-1"></i>Hủy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Table Layout (hidden on mobile) -->
        <div class="hidden sm:block overflow-x-auto overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <!-- Empty State -->
            <div v-if="!loading && filteredComments.length === 0" class="p-8 text-center">
                <i class="fas fa-comments text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-600">Không có đánh giá nào</p>
            </div>
            <!-- Table Content + Skeleton -->
            <table v-else class="w-full text-sm border-collapse">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left w-12"><input type="checkbox" /></th>
                        <th class="px-4 py-2 text-left">Điểm đánh giá</th>
                        <th class="px-4 py-2 text-left">Nội dung đánh giá</th>
                        <th class="px-4 py-2 text-center">Thời gian</th>
                        <th class="px-4 py-2 text-center">Trạng thái</th>
                        <th class="px-4 py-2 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="loading">
                        <tr v-for="n in 5" :key="n">
                            <td class="px-4 py-2">
                                <div class="bg-gray-200 rounded w-6 h-6 animate-pulse mx-auto"></div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="bg-gray-200 h-4 rounded w-16 animate-pulse"></div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="bg-gray-200 h-4 rounded w-2/3 mb-2 animate-pulse"></div>
                                <div class="bg-gray-200 h-3 rounded w-1/3 animate-pulse"></div>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <div class="bg-gray-200 h-4 rounded w-12 animate-pulse mx-auto"></div>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <div class="bg-gray-200 h-4 rounded w-16 animate-pulse mx-auto"></div>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <div class="bg-gray-200 h-4 rounded w-16 animate-pulse mx-auto"></div>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr v-for="comment in filteredComments" :key="comment.id"
                            :class="['border-b border-gray-300', comment.status === 'rejected' ? 'bg-red-50' : comment.status === 'approved' ? 'bg-blue-50' : '']">
                            <td class="px-4 py-2"><input type="checkbox" /></td>
                            <td class="px-4 py-2">
                                <span v-html="renderStars(comment.rating)"></span>
                            </td>
                            <td class="px-4 py-2">
                                <div class="mb-1">{{ comment.content }}</div>
                                <div v-if="comment.images && comment.images.length" class="mt-2">
                                    <div class="text-xs mb-1 font-semibold">Hình ảnh đánh giá:</div>
                                    <div class="flex gap-2">
                                        <img v-for="img in comment.images" :key="img.id"
                                            :src="getImageUrl(img.image_path)" class="w-16 h-16 object-cover rounded"
                                            alt="review image" />
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500">
                                    - <span class="font-semibold">{{ comment.userEmail || comment.userName }}</span>
                                    đánh giá sản phẩm <span class="text-blue-600 hover:underline">{{
                                        comment.productInfo?.name }}</span>
                                </div>
                                <!-- Admin reply -->
                                <div v-if="comment.reply" class="mt-2 ml-4 p-2 bg-gray-100 rounded">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-primary">Phản hồi admin:</span>
                                        <span v-if="!comment.isEditingReply">{{ comment.reply.content }}</span>
                                        <input v-else v-model="comment.editReplyText"
                                            class="border rounded px-2 py-1 text-xs flex-1" />
                                        <span class="text-xs text-gray-400">({{ comment.reply.date }})</span>
                                        <button v-if="!comment.isEditingReply" @click="startEditReply(comment)"
                                            class="bg-blue-500 text-white rounded px-2 py-1 text-xs ml-2">Sửa</button>
                                        <template v-else>
                                            <button @click="saveEditReply(comment)"
                                                class="bg-primary text-white rounded px-2 py-1 text-xs ml-2">Lưu</button>
                                            <button @click="cancelEditReply(comment)"
                                                class="bg-gray-400 text-white rounded px-2 py-1 text-xs ml-1">Hủy</button>
                                        </template>
                                    </div>
                                </div>
                                <!-- Reply form -->
                                <div v-else class="mt-2 ml-4">
                                    <div class="flex gap-2">
                                        <input type="text" v-model="comment.replyText" placeholder="Nhập phản hồi ..."
                                            class="flex-1 border border-gray-300 rounded px-3 py-1 text-xs focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100">
                                        <button @click="addReply(comment)"
                                            class="bg-primary text-white rounded px-3 py-1 text-xs cursor-pointer">Gửi</button>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="font-medium">{{ comment.date }}</span>
                                    <span v-if="comment.time" class="text-xs text-gray-500">{{ comment.time
                                    }}</span>
                                    <span v-if="isRecentReview(comment.date)"
                                        class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full mt-1">Mới</span>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span :class="[
                                    'px-2 py-1 rounded-full text-xs',
                                    {
                                        'bg-yellow-100 text-yellow-700 border border-yellow-300': comment.status === 'pending',
                                        'bg-green-100 text-green-700 border border-green-300': comment.status === 'approved',
                                        'bg-red-100 text-red-700 border border-red-300': comment.status === 'rejected'
                                    }
                                ]">
                                    {{ getStatusText(comment.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <button v-if="comment.status === 'pending'"
                                    @click="updateStatus(comment.id, 'approved')"
                                    class="bg-green-100 text-green-700 border border-green-300 rounded px-2 py-1 mr-1 text-xs">Duyệt</button>
                                <button v-if="comment.status === 'approved'"
                                    @click="updateStatus(comment.id, 'pending')"
                                    class="bg-yellow-100 text-yellow-700 border border-yellow-300 rounded px-2 py-1 mr-1 text-xs">Bỏ
                                    duyệt</button>
                                <button v-if="comment.status !== 'rejected'"
                                    @click="updateStatus(comment.id, 'rejected')"
                                    class="bg-red-100 text-red-700 border border-red-300 rounded px-2 py-1 mr-1 text-xs">Ẩn</button>
                                <button v-if="comment.status === 'rejected'"
                                    @click="updateStatus(comment.id, 'pending')"
                                    class="bg-blue-100 text-blue-700 border border-blue-300 rounded px-2 py-1 mr-1 text-xs">Bỏ
                                    ẩn</button>
                                <button @click="deleteComment(comment.id)"
                                    class="bg-red-100 text-red-700 border border-red-300 rounded px-2 py-1 text-xs"><i
                                        class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL; // ✅ Thay useRuntimeConfig
const adminAvatar = ref("https://randomuser.me/api/portraits/men/1.jpg");

const props = defineProps({
    comments: { type: Array, required: true },
    pagination: { type: Object, default: null },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits([
    "update-status",
    "delete",
    "add-reply",
    "update-reply",
    "page-change",
]);

const searchQuery = ref("");
const filterStatus = ref("");
const filterRating = ref("");
const filterHasImage = ref("");
const filterUnread = ref("");
const filterBadwords = ref("");
const filterHidden = ref("");
const filterApproval = ref("");

const getImageUrl = (url) => {
    if (!url) return "https://via.placeholder.com/150";
    if (url.startsWith("http")) return url;
    if (url.startsWith("review_images/")) {
        return `${API_BASE_URL}/storage/${url}`;
    }
    if (url.startsWith("storage/")) {
        return `${API_BASE_URL}/${url}`;
    }
    return `${API_BASE_URL}/storage/${url.replace(/^\/storage\//, "")}`;
};

const filteredComments = computed(() => {
    let filtered = props.comments;

    if (searchQuery.value) {
        filtered = filtered.filter(
            (comment) =>
                comment.content
                    .toLowerCase()
                    .includes(searchQuery.value.toLowerCase()) ||
                comment.userName
                    .toLowerCase()
                    .includes(searchQuery.value.toLowerCase())
        );
    }

    if (filterStatus.value) {
        filtered = filtered.filter((comment) => comment.status === filterStatus.value);
    }

    if (filterRating.value) {
        filtered = filtered.filter(
            (comment) => comment.rating === parseInt(filterRating.value)
        );
    }

    if (filterHasImage.value) {
        if (filterHasImage.value === "yes") {
            filtered = filtered.filter(
                (comment) => comment.images && comment.images.length > 0
            );
        } else if (filterHasImage.value === "no") {
            filtered = filtered.filter(
                (comment) => !comment.images || comment.images.length === 0
            );
        }
    }

    if (filterUnread.value) {
        filtered = filtered.filter(
            (comment) => comment.status === "pending" && !comment.isRead
        );
    }

    if (filterBadwords.value === "1") {
        filtered = filtered.filter(
            (comment) => comment.isHidden && !comment.isApproved
        );
    }

    if (filterHidden.value) {
        filtered = filtered.filter(
            (comment) => comment.isHidden === (filterHidden.value === "hidden")
        );
    }

    if (filterApproval.value) {
        filtered = filtered.filter(
            (comment) => comment.status === filterApproval.value
        );
    }

    return filtered;
});

const getStatusText = (status) => {
    switch (status) {
        case "pending":
            return "Chờ duyệt";
        case "approved":
            return "Đã duyệt";
        case "rejected":
            return "Vi phạm (ẩn)";
        default:
            return status;
    }
};

const updateStatus = (id, newStatus) => {
    emit("update-status", { id, status: newStatus });
};

const deleteComment = (id) => {
    if (confirm("Bạn có chắc chắn muốn xóa bình luận này?")) {
        emit("delete", id);
    }
};

const addReply = (comment) => {
    if (!comment.replyText.trim()) return;
    emit("add-reply", { id: comment.id, content: comment.replyText });
};

// Mobile-specific methods
const toggleReplyForm = (comment) => {
    comment.showReplyForm = !comment.showReplyForm;
    if (!comment.showReplyForm) {
        comment.replyText = '';
    }
};

const submitReply = (comment) => {
    if (comment.replyText.trim()) {
        addReply(comment);
        comment.showReplyForm = false;
        comment.replyText = '';
    }
};

const renderStars = (rating) => {
    return Array.from({ length: 5 }, (_, index) =>
        `<i class="fas fa-star" style="color: ${index < rating ? "#ffd700" : "#ccc"
        };"></i>`
    ).join("");
};

const startEditReply = (comment) => {
    comment.isEditingReply = true;
    comment.editReplyText = comment.reply.content;
};

const cancelEditReply = (comment) => {
    comment.isEditingReply = false;
    comment.editReplyText = "";
};

const saveEditReply = (comment) => {
    if (!comment.editReplyText.trim()) return;
    emit("update-reply", { id: comment.id, content: comment.editReplyText });
    comment.isEditingReply = false;
};

const isRecentReview = (date) => {
    const reviewDate = new Date(date);
    const now = new Date();
    const diffTime = Math.abs(now - reviewDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays <= 7;
};

watch(filterBadwords, (val) => {
    if (val === "1") {
        emit("page-change", { badwords: 1 });
    } else {
        emit("page-change", {});
    }
});
</script>

<style scoped>
.bg-primary {
    background-color: #3bb77e;
}
</style>