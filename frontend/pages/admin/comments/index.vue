<template>
    <div class="comments-page">
        <div class="page-header tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Bình luận</h1>
                <p class="tw-text-gray-600">Quản lý bình luận sản phẩm</p>
            </div>
        </div>

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
                                <img :src="comment.userAvatar" :alt="comment.userName"
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
                                        <img :src="comment.productInfo.image" :alt="comment.productInfo.name"
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
                                    <button v-if="comment.status === 'pending'"
                                        @click="updateStatus(comment.id, 'approved')"
                                        class="tw-bg-green-500 tw-text-white tw-rounded tw-px-2 tw-py-1">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button v-if="comment.status === 'pending'"
                                        @click="updateStatus(comment.id, 'rejected')"
                                        class="tw-bg-red-500 tw-text-white tw-rounded tw-px-2 tw-py-1">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <button @click="deleteComment(comment.id)"
                                        class="tw-bg-gray-500 tw-text-white tw-rounded tw-px-2 tw-py-1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Reply section -->
                        <div v-if="comment.reply" class="tw-mt-4 tw-ml-16 tw-pl-4 tw-border-l-2 tw-border-gray-200">
                            <div class="tw-flex tw-items-start tw-gap-2">
                                <img src="https://randomuser.me/api/portraits/men/1.jpg" alt="Admin"
                                    class="tw-w-8 tw-h-8 tw-rounded-full">
                                <div>
                                    <div class="tw-flex tw-items-center tw-gap-2">
                                        <span class="tw-font-medium">Admin</span>
                                        <span class="tw-text-sm tw-text-gray-500">{{ comment.reply.date }}</span>
                                    </div>
                                    <p class="tw-text-gray-600">{{ comment.reply.content }}</p>
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
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import { ref, computed } from 'vue'

const searchQuery = ref('')
const filterStatus = ref('')
const filterRating = ref('')

// Mock data
const comments = ref([
    {
        id: 1,
        userName: 'Nguyễn Văn A',
        userAvatar: 'https://randomuser.me/api/portraits/men/1.jpg',
        rating: 5,
        content: 'Sản phẩm rất tốt, đóng gói cẩn thận, giao hàng nhanh!',
        date: '2024-03-15',
        status: 'pending',
        productInfo: {
            name: 'iPhone 15 Pro Max',
            image: 'https://via.placeholder.com/150'
        },
        reply: null,
        replyText: ''
    },
    {
        id: 2,
        userName: 'Trần Thị B',
        userAvatar: 'https://randomuser.me/api/portraits/women/1.jpg',
        rating: 4,
        content: 'Sản phẩm tốt, nhưng giao hàng hơi chậm',
        date: '2024-03-14',
        status: 'approved',
        productInfo: {
            name: 'Samsung Galaxy S24 Ultra',
            image: 'https://via.placeholder.com/150'
        },
        reply: {
            content: 'Cảm ơn bạn đã góp ý. Chúng tôi sẽ cải thiện dịch vụ giao hàng.',
            date: '2024-03-14'
        },
        replyText: ''
    }
])

const filteredComments = computed(() => {
    return comments.value.filter(comment => {
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
    const comment = comments.value.find(c => c.id === id)
    if (comment) {
        comment.status = newStatus
    }
}

const deleteComment = (id) => {
    if (confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
        const index = comments.value.findIndex(c => c.id === id)
        if (index !== -1) {
            comments.value.splice(index, 1)
        }
    }
}

const addReply = (comment) => {
    if (!comment.replyText.trim()) return

    comment.reply = {
        content: comment.replyText,
        date: new Date().toISOString().split('T')[0]
    }
    comment.replyText = ''
}
</script>

<style scoped>
.comments-page {
    padding: 1.5rem;
}

.tw-bg-primary {
    background-color: #3bb77e;
}
</style>