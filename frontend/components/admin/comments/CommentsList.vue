<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow">
        <!-- Filter Section -->
        <div class="tw-p-4 tw-border-b">
            <div class="tw-flex tw-gap-4">
                <div class="tw-relative">
                    <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..."
                        class="tw-border tw-rounded tw-px-4 tw-py-2 tw-pl-10 tw-w-64">
                    <i
                        class="fas fa-search tw-absolute tw-left-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-text-gray-400"></i>
                </div>
                <select v-model="filterStatus" class="tw-border tw-rounded tw-px-4 tw-py-2">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending">Chờ duyệt</option>
                    <option value="approved">Đã duyệt</option>
                    <option value="rejected">Đã từ chối</option>
                </select>
                <select v-model="filterRating" class="tw-border tw-rounded tw-px-4 tw-py-2">
                    <option value="">Tất cả đánh giá</option>
                    <option value="5">5 sao</option>
                    <option value="4">4 sao</option>
                    <option value="3">3 sao</option>
                    <option value="2">2 sao</option>
                    <option value="1">1 sao</option>
                </select>
            </div>
        </div>

        <!-- Comments List -->
        <div class="tw-p-4">
            <div class="tw-space-y-4">
                <div v-for="comment in filteredComments" :key="comment.id" class="tw-border tw-rounded-lg tw-p-4">
                    <div class="tw-flex tw-justify-between tw-items-start">
                        <div class="tw-flex tw-gap-4">
                            <img :src="getImageUrl(comment.userAvatar)" :alt="comment.userName"
                                class="tw-w-12 tw-h-12 tw-rounded-full tw-object-cover">
                            <div>
                                <div class="tw-flex tw-items-center tw-gap-2">
                                    <h3 class="tw-font-semibold">{{ comment.userName }}</h3>
                                    <span class="tw-text-sm tw-text-gray-500">{{ comment.date }}</span>
                                </div>
                                <div class="tw-flex tw-items-center tw-gap-1 tw-text-yellow-400">
                                    <i v-for="n in 5" :key="n"
                                        :class="['fas', n <= comment.rating ? 'fa-star' : 'fa-star tw-text-gray-300']">
                                    </i>
                                </div>
                                <p class="tw-mt-2">{{ comment.content }}</p>
                                <div v-if="comment.productInfo" class="tw-mt-2 tw-flex tw-items-center tw-gap-2">
                                    <img :src="getImageUrl(comment.productInfo.image)" :alt="comment.productInfo.name"
                                        class="tw-w-10 tw-h-10 tw-object-cover tw-rounded">
                                    <span class="tw-text-sm tw-text-gray-600">{{ comment.productInfo.name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="tw-flex tw-flex-col tw-items-end tw-gap-2">
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
                            <div class="tw-flex tw-gap-2">
                                <button v-if="comment.status !== 'approved'"
                                    @click="updateStatus(comment.id, 'approved')"
                                    class="tw-bg-green-500 tw-text-white tw-rounded tw-px-2 tw-py-1 tw-flex tw-items-center tw-gap-1">
                                    <i class="fas fa-check"></i>
                                    <span class="tw-text-xs">Phê duyệt</span>
                                </button>
                                <button v-if="comment.status !== 'rejected'"
                                    @click="updateStatus(comment.id, 'rejected')"
                                    class="tw-bg-red-500 tw-text-white tw-rounded tw-px-2 tw-py-1 tw-flex tw-items-center tw-gap-1">
                                    <i class="fas fa-times"></i>
                                    <span class="tw-text-xs">Từ chối</span>
                                </button>
                                <button @click="deleteComment(comment.id)"
                                    class="tw-bg-gray-500 tw-text-white tw-rounded tw-px-2 tw-py-1 tw-flex tw-items-center tw-gap-1">
                                    <i class="fas fa-trash"></i>
                                    <span class="tw-text-xs">Xóa</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Reply section -->
                    <div v-if="comment.reply" class="tw-mt-4 tw-ml-16 tw-pl-4 tw-border-l-2 tw-border-gray-200">
                        <div class="tw-flex tw-items-start tw-gap-2">
                            <img :src="adminAvatar" alt="Admin"
                                class="tw-w-8 tw-h-8 tw-rounded-full">
                            <div class="tw-flex-1">
                                <div class="tw-flex tw-items-center tw-justify-between">
                                    <div class="tw-flex tw-items-center tw-gap-2">
                                        <span class="tw-font-medium">Admin</span>
                                        <span class="tw-text-sm tw-text-gray-500">{{ comment.reply.date }}</span>
                                    </div>
                                    <div class="tw-flex tw-gap-2">
                                        <button v-if="!comment.isEditing" @click="startEditReply(comment)"
                                            class="tw-bg-blue-500 tw-text-white tw-rounded tw-px-2 tw-py-1 tw-flex tw-items-center tw-gap-1 tw-text-xs">
                                            <i class="fas fa-edit"></i>
                                            <span>Sửa</span>
                                        </button>
                                    </div>
                                </div>
                                <div v-if="!comment.isEditing">
                                    <p class="tw-text-gray-600">{{ comment.reply.content }}</p>
                                </div>
                                <div v-else class="tw-mt-2">
                                    <div class="tw-flex tw-gap-2">
                                        <input type="text" v-model="comment.editReplyText" 
                                            class="tw-flex-1 tw-border tw-rounded tw-px-3 tw-py-1">
                                        <button @click="updateReply(comment)"
                                            class="tw-bg-primary tw-text-white tw-rounded tw-px-3 tw-py-1 tw-text-xs">
                                            Lưu
                                        </button>
                                        <button @click="cancelEditReply(comment)"
                                            class="tw-bg-gray-500 tw-text-white tw-rounded tw-px-3 tw-py-1 tw-text-xs">
                                            Hủy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Reply form -->
                    <div v-if="!comment.reply" class="tw-mt-4 tw-ml-16">
                        <div class="tw-flex tw-gap-2">
                            <input type="text" v-model="comment.replyText" placeholder="Nhập phản hồi..."
                                class="tw-flex-1 tw-border tw-rounded tw-px-3 tw-py-1">
                            <button @click="addReply(comment)"
                                class="tw-bg-primary tw-text-white tw-rounded tw-px-3 tw-py-1">
                                Gửi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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

const getImageUrl = (url) => {
    if (!url) return 'https://via.placeholder.com/150'
    
    if (url.startsWith('http')) {
        const baseUrl = runtimeConfig.public.apiBaseUrl
        
        if (url.includes(`${baseUrl}/storage/${baseUrl}/storage/`)) {
            return url.replace(new RegExp(`(${baseUrl}/storage/)+`, 'g'), `${baseUrl}/storage/`)
        }
        
        if (url.includes(`${baseUrl}/storage/`) && !url.startsWith(`${baseUrl}/storage/`)) {
            return url.replace(`${baseUrl}/storage/`, '')
        }
        
        return url
    }
    
    const baseUrl = runtimeConfig.public.apiBaseUrl
    return `${baseUrl}/storage/${url.replace(/^\/storage\//, '')}`
}

const filteredComments = computed(() => {
    return props.comments.filter(comment => {
        const matchesSearch = comment.content.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            comment.userName.toLowerCase().includes(searchQuery.value.toLowerCase())
        const matchesStatus = !filterStatus.value || comment.status === filterStatus.value
        const matchesRating = !filterRating.value || comment.rating === parseInt(filterRating.value)
        return matchesSearch && matchesStatus && matchesRating
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

const startEditReply = (comment) => {
    comment.isEditing = true
    comment.editReplyText = comment.reply.content
}

const cancelEditReply = (comment) => {
    comment.isEditing = false
    comment.editReplyText = ''
}

const updateReply = (comment) => {
    if (!comment.editReplyText.trim()) return
    emit('update-reply', { id: comment.id, content: comment.editReplyText })
    comment.isEditing = false
}
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}
</style>