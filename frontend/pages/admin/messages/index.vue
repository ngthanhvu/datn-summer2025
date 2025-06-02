<template>
    <div class="messages-page tw-flex">
        <MessageSidebar :messages="messages" :selected-message-id="selectedMessage?.id" @select="selectMessage" />
        <MessageContent :message="selectedMessage" :admin-avatar="adminAvatar" @send="sendMessage" />
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import { ref } from 'vue'
import MessageSidebar from '~/components/admin/messages/MessageSidebar.vue'
import MessageContent from '~/components/admin/messages/MessageContent.vue'

const selectedMessage = ref(null)
const adminAvatar = 'https://randomuser.me/api/portraits/men/1.jpg'

// Mock data
const messages = ref([
    {
        id: 1,
        name: 'Nguyễn Văn A',
        email: 'nguyenvana@example.com',
        avatar: 'https://randomuser.me/api/portraits/men/2.jpg',
        lastMessage: 'Xin chào, tôi cần hỗ trợ về đơn hàng',
        time: '10:30 AM',
        read: false,
        messages: [
            {
                content: 'Xin chào, tôi cần hỗ trợ về đơn hàng #123',
                time: '10:30 AM',
                isAdmin: false
            },
            {
                content: 'Vâng, tôi có thể giúp gì cho bạn?',
                time: '10:31 AM',
                isAdmin: true
            },
            {
                content: 'Đơn hàng của tôi đã 3 ngày chưa được giao',
                time: '10:32 AM',
                isAdmin: false
            }
        ]
    },
    {
        id: 2,
        name: 'Trần Thị B',
        email: 'tranthib@example.com',
        avatar: 'https://randomuser.me/api/portraits/women/2.jpg',
        lastMessage: 'Cảm ơn bạn đã hỗ trợ',
        time: '09:15 AM',
        read: true,
        messages: [
            {
                content: 'Tôi muốn đổi sản phẩm',
                time: '09:10 AM',
                isAdmin: false
            },
            {
                content: 'Bạn vui lòng cho mình biết lý do đổi trả',
                time: '09:12 AM',
                isAdmin: true
            },
            {
                content: 'Sản phẩm bị lỗi màu',
                time: '09:13 AM',
                isAdmin: false
            },
            {
                content: 'Cảm ơn bạn đã hỗ trợ',
                time: '09:15 AM',
                isAdmin: false
            }
        ]
    }
])

const selectMessage = (message) => {
    selectedMessage.value = message
    // Mark as read
    message.read = true
}

const sendMessage = (content) => {
    selectedMessage.value.messages.push({
        content,
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        isAdmin: true
    })

    // Update last message
    selectedMessage.value.lastMessage = content
    selectedMessage.value.time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
.messages-page {
    height: calc(100vh - 64px);
}

::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}

.tw-bg-primary\/10 {
    background-color: rgba(59, 183, 126, 0.1);
}

.message-list::-webkit-scrollbar {
    width: 4px;
}
</style>