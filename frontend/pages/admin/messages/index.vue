<template>
    <div class="messages-page tw-flex">
        <!-- Sidebar -->
        <div class="messages-sidebar tw-w-[320px] tw-bg-white tw-border-r tw-flex tw-flex-col tw-h-[calc(100vh-64px)]">
            <div class="tw-p-4 tw-border-b">
                <div class="tw-relative">
                    <input type="text" v-model="searchQuery" placeholder="Tìm kiếm tin nhắn..."
                        class="tw-w-full tw-border tw-rounded tw-px-4 tw-py-2 tw-pl-10">
                    <i
                        class="fas fa-search tw-absolute tw-left-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-text-gray-400"></i>
                </div>
            </div>

            <div class="message-list tw-flex-1 tw-overflow-y-auto">
                <div v-for="message in messages" :key="message.id" @click="selectMessage(message)"
                    class="message-item tw-p-4 tw-border-b tw-cursor-pointer"
                    :class="[selectedMessage?.id === message.id ? 'tw-bg-primary/10' : 'hover:tw-bg-gray-50']">
                    <div class="tw-flex tw-gap-3">
                        <div class="tw-relative">
                            <img :src="message.avatar" :alt="message.name"
                                class="tw-w-12 tw-h-12 tw-rounded-full tw-object-cover">
                            <div v-if="!message.read"
                                class="tw-w-3 tw-h-3 tw-bg-primary tw-rounded-full tw-absolute tw-right-0 tw-bottom-0 tw-border-2 tw-border-white">
                            </div>
                        </div>
                        <div class="tw-flex-1 tw-min-w-0">
                            <div class="tw-flex tw-justify-between tw-items-start">
                                <h3 class="tw-font-medium tw-truncate">{{ message.name }}</h3>
                                <span class="tw-text-xs tw-text-gray-500 tw-whitespace-nowrap tw-ml-2">{{ message.time
                                }}</span>
                            </div>
                            <p class="tw-text-sm tw-text-gray-600 tw-truncate">{{ message.lastMessage }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="messages-content tw-flex-1 tw-bg-gray-50 tw-flex tw-flex-col tw-h-[calc(100vh-64px)]">
            <div v-if="selectedMessage" class="tw-h-full tw-flex tw-flex-col">
                <!-- Message Header -->
                <div class="tw-bg-white tw-px-6 tw-py-4 tw-border-b">
                    <div class="tw-flex tw-items-center tw-gap-4">
                        <img :src="selectedMessage.avatar" :alt="selectedMessage.name"
                            class="tw-w-12 tw-h-12 tw-rounded-full tw-object-cover">
                        <div>
                            <h3 class="tw-font-semibold">{{ selectedMessage.name }}</h3>
                            <p class="tw-text-sm tw-text-gray-500">{{ selectedMessage.email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="tw-flex-1 tw-overflow-y-auto tw-p-6">
                    <div class="tw-max-w-3xl tw-mx-auto tw-space-y-4">
                        <div v-for="(msg, index) in selectedMessage.messages" :key="index" class="tw-flex"
                            :class="[msg.isAdmin ? 'tw-justify-end' : 'tw-justify-start']">
                            <div class="tw-flex tw-items-end tw-gap-2"
                                :class="[msg.isAdmin ? 'tw-flex-row-reverse' : '']">
                                <img :src="msg.isAdmin ? adminAvatar : selectedMessage.avatar"
                                    :alt="msg.isAdmin ? 'Admin' : selectedMessage.name"
                                    class="tw-w-8 tw-h-8 tw-rounded-full tw-object-cover">
                                <div :class="[
                                    'tw-max-w-md tw-rounded-2xl tw-px-4 tw-py-2',
                                    msg.isAdmin ? 'tw-bg-primary tw-text-white tw-rounded-br-none' : 'tw-bg-white tw-rounded-bl-none'
                                ]">
                                    <p>{{ msg.content }}</p>
                                    <span class="tw-text-xs tw-opacity-75 tw-block tw-mt-1">{{ msg.time }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="tw-bg-white tw-px-6 tw-py-4 tw-border-t">
                    <div class="tw-max-w-3xl tw-mx-auto">
                        <div class="tw-flex tw-gap-4">
                            <input type="text" v-model="newMessage" @keyup.enter="sendMessage"
                                placeholder="Nhập tin nhắn..."
                                class="tw-flex-1 tw-border tw-rounded-full tw-px-6 tw-py-3 focus:tw-ring-2 focus:tw-ring-primary/20 focus:tw-border-primary">
                            <button @click="sendMessage"
                                class="tw-bg-primary tw-text-white tw-rounded-full tw-w-12 tw-h-12 tw-flex tw-items-center tw-justify-center hover:tw-bg-primary-dark">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="tw-h-full tw-flex tw-items-center tw-justify-center tw-text-gray-500">
                <div class="tw-text-center">
                    <i class="fas fa-comments tw-text-4xl tw-mb-2"></i>
                    <p>Chọn một cuộc trò chuyện để bắt đầu</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import { ref } from 'vue'

const searchQuery = ref('')
const selectedMessage = ref(null)
const newMessage = ref('')
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

const sendMessage = () => {
    if (!newMessage.value.trim()) return

    selectedMessage.value.messages.push({
        content: newMessage.value,
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        isAdmin: true
    })

    // Update last message
    selectedMessage.value.lastMessage = newMessage.value
    selectedMessage.value.time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })

    newMessage.value = ''
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