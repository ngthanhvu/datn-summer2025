<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow">
        <!-- Filter Row -->
        <div class="tw-p-4 tw-border-b tw-bg-gray-50">
            <div class="tw-flex tw-gap-2 tw-items-center">
                <select v-model="filterStatus" class="tw-border tw-rounded tw-px-3 tw-py-1 tw-text-sm">
                    <option value="">Trạng thái</option>
                    <option value="pending">Chờ duyệt</option>
                    <option value="approved">Hiển thị</option>
                    <option value="rejected">Vi phạm</option>
                </select>
                <select v-model="filterRating" class="tw-border tw-rounded tw-px-3 tw-py-1 tw-text-sm">
                    <option value="">Điểm đánh giá</option>
                    <option v-for="n in 5" :key="n" :value="n">{{ n }} sao</option>
                </select>
                <select v-model="filterHasImage" class="tw-border tw-rounded tw-px-3 tw-py-1 tw-text-sm">
                    <option value="">Có hình ảnh</option>
                    <option value="yes">Có</option>
                    <option value="no">Không</option>
                </select>
                <select v-model="filterUnread" class="tw-border tw-rounded tw-px-3 tw-py-1 tw-text-sm">
                    <option value="">Chưa đọc</option>
                    <option value="yes">Chưa đọc</option>
                </select>
                <input v-model="searchQuery" type="text" placeholder="Nhập từ khóa tìm kiếm ..." class="tw-border tw-rounded tw-px-3 tw-py-1 tw-text-sm tw-w-64" />
            </div>
        </div>
        <!-- Table -->
        <div class="tw-p-0">
            <table class="tw-w-full tw-text-sm tw-border-collapse">
                <thead>
                    <tr class="tw-bg-gray-50">
                        <th class="tw-px-4 tw-py-2 tw-text-left tw-w-12"><input type="checkbox" /></th>
                        <th class="tw-px-4 tw-py-2 tw-text-left">Điểm đánh giá</th>
                        <th class="tw-px-4 tw-py-2 tw-text-left">Nội dung đánh giá</th>
                        <th class="tw-px-4 tw-py-2 tw-text-center">Thời gian</th>
                        <th class="tw-px-4 tw-py-2 tw-text-center">Trạng thái</th>
                        <th class="tw-px-4 tw-py-2 tw-text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="comment in filteredComments" :key="comment.id" :class="['tw-border-b', comment.status === 'rejected' ? 'tw-bg-red-50' : comment.status === 'approved' ? 'tw-bg-blue-50' : '']">
                        <td class="tw-px-4 tw-py-2"><input type="checkbox" /></td>
                        <td class="tw-px-4 tw-py-2">
                            <span v-html="renderStars(comment.rating)"></span>
                        </td>
                        <td class="tw-px-4 tw-py-2">
                            <div class="tw-mb-1">{{ comment.content }}</div>
                            <div v-if="comment.images && comment.images.length" class="tw-mt-2">
                                <div class="tw-text-xs tw-mb-1 tw-font-semibold">Hình ảnh đánh giá:</div>
                                <div class="tw-flex tw-gap-2">
                                    <img
                                        v-for="img in comment.images"
                                        :key="img.id"
                                        :src="getImageUrl(img.image_path)"
                                        class="tw-w-16 tw-h-16 tw-object-cover tw-rounded"
                                        alt="review image"
                                    />
                                </div>
                            </div>
                            <div class="tw-text-xs tw-text-gray-500">
                                - <span class="tw-font-semibold">{{ comment.userEmail || comment.userName }}</span>
                                đánh giá sản phẩm <span class="tw-text-blue-600 hover:tw-underline">{{ comment.productInfo?.name }}</span>
                            </div>
                            <!-- Admin reply -->
                            <div v-if="comment.reply" class="tw-mt-2 tw-ml-4 tw-p-2 tw-bg-gray-100 tw-rounded">
                                <div class="tw-flex tw-items-center tw-gap-2">
                                    <span class="tw-font-semibold tw-text-primary">Phản hồi admin:</span>
                                    <span v-if="!comment.isEditingReply">{{ comment.reply.content }}</span>
                                    <input v-else v-model="comment.editReplyText" class="tw-border tw-rounded tw-px-2 tw-py-1 tw-text-xs tw-flex-1" />
                                    <span class="tw-text-xs tw-text-gray-400">({{ comment.reply.date }})</span>
                                    <button v-if="!comment.isEditingReply" @click="startEditReply(comment)" class="tw-bg-blue-500 tw-text-white tw-rounded tw-px-2 tw-py-1 tw-text-xs ml-2">Sửa</button>
                                    <template v-else>
                                        <button @click="saveEditReply(comment)" class="tw-bg-primary tw-text-white tw-rounded tw-px-2 tw-py-1 tw-text-xs ml-2">Lưu</button>
                                        <button @click="cancelEditReply(comment)" class="tw-bg-gray-400 tw-text-white tw-rounded tw-px-2 tw-py-1 tw-text-xs ml-1">Hủy</button>
                                    </template>
                                </div>
                            </div>
                            <!-- Reply form -->
                            <div v-else class="tw-mt-2 tw-ml-4">
                                <div class="tw-flex tw-gap-2">
                                    <input type="text" v-model="comment.replyText" placeholder="Nhập phản hồi ..." class="tw-flex-1 tw-border tw-rounded tw-px-3 tw-py-1 tw-text-xs">
                                    <button @click="addReply(comment)" class="tw-bg-primary tw-text-white tw-rounded tw-px-3 tw-py-1 tw-text-xs">Gửi</button>
                                </div>
                            </div>
                        </td>
                        <td class="tw-px-4 tw-py-2 tw-text-center">{{ comment.date }}</td>
                        <td class="tw-px-4 tw-py-2 tw-text-center">
                            <span :class="[
                                'tw-px-2 tw-py-1 tw-rounded-full tw-text-xs',
                                {
                                    'tw-bg-yellow-100 tw-text-yellow-700': comment.status === 'pending',
                                    'tw-bg-green-100 tw-text-green-700': comment.status === 'approved',
                                    'tw-bg-red-100 tw-text-red-700': comment.status === 'rejected'
                                }
                            ]">
                                {{ getStatusText(comment.status) }}
                            </span>
                        </td>
                        <td class="tw-px-4 tw-py-2 tw-text-center">
                            <button v-if="comment.status !== 'approved'" @click="updateStatus(comment.id, 'approved')" class="tw-bg-green-100 tw-text-green-700 tw-rounded tw-px-2 tw-py-1 tw-mr-1 tw-text-xs">Hiển thị</button>
                            <button v-if="comment.status !== 'rejected'" @click="updateStatus(comment.id, 'rejected')" class="tw-bg-red-100 tw-text-red-700 tw-rounded tw-px-2 tw-py-1 tw-mr-1 tw-text-xs">Ẩn</button>
                            <button @click="deleteComment(comment.id)" class="tw-bg-gray-100 tw-text-gray-700 tw-rounded tw-px-2 tw-py-1 tw-text-xs"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRuntimeConfig } from 'nuxt/app'

const runtimeConfig = useRuntimeConfig()
const adminAvatar = ref('https://randomuser.me/api/portraits/men/1.jpg')

const props = defineProps({
    comments: {
        type: Array,
        required: true
    }
})

const emit = defineEmits(['update-status', 'delete', 'add-reply', 'update-reply'])

const searchQuery = ref('')
const filterStatus = ref('')
const filterRating = ref('')
const filterHasImage = ref('')
const filterUnread = ref('')

const getImageUrl = (url) => {
    if (!url) return 'https://via.placeholder.com/150'
    // Nếu đã là URL tuyệt đối
    if (url.startsWith('http')) return url
    if (url.startsWith('review_images/')) {
        return `${runtimeConfig.public.apiBaseUrl}/storage/${url}`
    }
    if (url.startsWith('storage/')) {
        return `${runtimeConfig.public.apiBaseUrl}/${url}`
    }
    return `${runtimeConfig.public.apiBaseUrl}/storage/${url.replace(/^\/storage\//, '')}`
}

const filteredComments = computed(() => {
    return props.comments.filter(comment => {
        const matchesSearch = comment.content.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            comment.userName.toLowerCase().includes(searchQuery.value.toLowerCase())
        const matchesStatus = !filterStatus.value || comment.status === filterStatus.value
        const matchesRating = !filterRating.value || comment.rating === parseInt(filterRating.value)
        const matchesHasImage = !filterHasImage.value || (comment.productInfo && comment.productInfo.image)
        const matchesUnread = !filterUnread.value || (comment.status === 'pending' && !comment.isRead)
        return matchesSearch && matchesStatus && matchesRating && matchesHasImage && matchesUnread
    })
})

const getStatusText = (status) => {
    switch (status) {
        case 'pending': return 'Chờ duyệt'
        case 'approved': return 'Đã duyệt'
        case 'rejected': return 'Đã từ chối'
        default: return status
    }
}

const updateStatus = (id, newStatus) => {
    emit('update-status', { id, status: newStatus })
}

const deleteComment = (id) => {
    if (confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
        emit('delete', id)
    }
}

const addReply = (comment) => {
    if (!comment.replyText.trim()) return
    emit('add-reply', { id: comment.id, content: comment.replyText })
}

const renderStars = (rating) => {
    return Array.from({ length: 5 }, (_, index) =>
        `<i class="fas fa-star" style="color: ${index < rating ? '#ffd700' : '#ccc'};"></i>`
    ).join('')
}

const startEditReply = (comment) => {
    comment.isEditingReply = true
    comment.editReplyText = comment.reply.content
}

const cancelEditReply = (comment) => {
    comment.isEditingReply = false
    comment.editReplyText = ''
}

const saveEditReply = (comment) => {
    if (!comment.editReplyText.trim()) return
    emit('update-reply', { id: comment.id, content: comment.editReplyText })
    comment.isEditingReply = false
}
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}
</style>