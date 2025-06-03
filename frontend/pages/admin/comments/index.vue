<template>
    <div class="comments-page">
        <div class="page-header tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Bình luận</h1>
                <p class="tw-text-gray-600">Quản lý bình luận sản phẩm</p>
            </div>
        </div>

        <CommentsList :comments="comments" @update-status="handleUpdateStatus" @delete="handleDelete"
            @add-reply="handleAddReply" />
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import { ref } from 'vue'
import CommentsList from '~/components/admin/comments/CommentsList.vue'

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

const handleUpdateStatus = ({ id, status }) => {
    const comment = comments.value.find(c => c.id === id)
    if (comment) {
        comment.status = status
    }
}

const handleDelete = (id) => {
    const index = comments.value.findIndex(c => c.id === id)
    if (index !== -1) {
        comments.value.splice(index, 1)
    }
}

const handleAddReply = ({ id, content }) => {
    const comment = comments.value.find(c => c.id === id)
    if (comment) {
        comment.reply = {
            content,
            date: new Date().toISOString().split('T')[0]
        }
        comment.replyText = ''
    }
}
</script>

<style scoped>
.comments-page {
    padding: 1.5rem;
}
</style>