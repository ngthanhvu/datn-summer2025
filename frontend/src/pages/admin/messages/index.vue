<template>
    <div class="admin-chat-container h-screen flex bg-gray-50">
        <!-- Sidebar - User Conversations -->
        <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col h-[calc(100vh-64px)]">
            <!-- Header -->
            <div class="p-4 border-b" style="background-color: #3BB77E; color: #fff;">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-comments text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold">Tin nhắn từ khách hàng</h2>
                        <p class="text-sm opacity-90">{{ conversations.length }} cuộc trò chuyện</p>
                    </div>
                </div>
            </div>

            <!-- Search (sticky) -->
            <div class="p-4 border-b border-gray-100 sticky top-[64px] z-10 bg-white">
                <div class="relative">
                    <input v-model="searchQuery" type="text" placeholder="Tìm kiếm khách hàng..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- Conversations List (scrollable) -->
            <div class="flex-1 overflow-y-auto">
                <!-- Loading State -->
                <div v-if="loading" class="p-4 text-center">
                    <i class="fas fa-spinner animate-spin text-xl text-gray-400 mb-2"></i>
                    <div class="text-gray-500">Đang tải cuộc trò chuyện...</div>
                </div>

                <!-- Empty State -->
                <div v-else-if="filteredConversations.length === 0" class="p-8 text-center text-gray-500">
                    <i class="fas fa-comment-slash text-4xl mb-3"></i>
                    <div class="font-medium mb-1">Chưa có tin nhắn nào</div>
                    <div class="text-sm">Tin nhắn từ khách hàng sẽ hiển thị ở đây</div>
                </div>

                <!-- Conversations -->
                <div v-else class="divide-y divide-gray-100">
                    <div v-for="conversation in filteredConversations" :key="conversation.user.id"
                        @click="selectConversation(conversation)" :class="[
                            'flex items-center gap-3 p-4 cursor-pointer transition-colors hover:bg-gray-50',
                            selectedUser?.id === conversation.user.id ? 'bg-green-50 border-r-4 border-[#3BB77E]' : ''
                        ]">
                        <div class="relative">
                            <img :src="conversation.user.avatar ? getUserAvatar(conversation.user.avatar) : 'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'"
                                :alt="conversation.user.username"
                                class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
                            <div v-if="conversation.unread_count > 0"
                                class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold">
                                {{ conversation.unread_count > 9 ? '9+' : conversation.unread_count }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <div class="font-medium text-gray-900 truncate">
                                    {{ conversation.user.username || conversation.user.username }}
                                </div>
                                <div class="text-xs text-gray-500 ml-2">
                                    <span v-if="conversation.latest_message && conversation.latest_message.sent_at">{{ formatTime(conversation.latest_message.sent_at) }}</span>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 truncate mt-1">
                                {{ conversation.latest_message.message }}
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                {{ conversation.user.email }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 w-0 flex flex-col h-full overflow-x-hidden bg-white">
            <div v-if="selectedUser" class="flex flex-col h-full">
                <div v-if="loadingMessages" class="flex-1 flex items-center justify-center">
                    <i class="fas fa-spinner animate-spin text-3xl text-gray-400 mb-2"></i>
                    <div class="text-gray-500 ml-2">Đang tải tin nhắn...</div>
                </div>
                <MessageContent
                    v-else
                    ref="messageContentRef"
                    :key="`messages-${selectedUser.id}-${messages.map(m => m.id).join('-')}`"
                    :message="{
                        name: selectedUser.username,
                        email: selectedUser.email,
                        avatar: getUserAvatar(selectedUser.avatar),
                        messages: [...computedMessages]
                    }"
                    :adminAvatar="adminAvatar"
                    @send="handleSendMessage"
                />
            </div>
            <div v-else class="flex-1 flex items-center justify-center bg-white">
                <div class="text-center text-gray-500">
                    <i class="fas fa-comment-dots text-6xl mb-4"></i>
                    <h3 class="text-xl font-medium mb-2">Chọn một cuộc trò chuyện</h3>
                    <p class="text-gray-400">Chọn khách hàng từ danh sách để bắt đầu chat</p>
                </div>
            </div>
        </div>

        <!-- Image Modal -->
        <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
            @click="closeImageModal">
            <img :src="modalImage" class="max-w-[90%] max-h-[90%] object-contain">
        </div>
    </div>
</template>

<script setup>
import { useHead } from '@vueuse/head'
useHead({
    title: "Tin nhắn | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Tin nhắn"
        }
    ]
})
import { ref, onMounted, computed, nextTick, watch, onUnmounted } from 'vue'
import { useChat } from '../../../composable/useChat'
import MessageContent from '../../../components/admin/messages/MessagesContent.vue'

function formatTime(timestamp) {
  if (!timestamp) return '';
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now - date;
  if (diff < 60000) return 'Vừa xong';
  if (diff < 3600000) return `${Math.floor(diff / 60000)} phút trước`;
  if (diff < 86400000) return `${Math.floor(diff / 3600000)} giờ trước`;
  return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
}

const currentAdmin = ref(null)
try {
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  if (user && user.id) currentAdmin.value = user
} catch {}

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

const {
    getConversations,
    getMessages,
    sendMessage: sendChatMessage
} = useChat()

const conversations = ref([])
const selectedUser = ref(null)
const messages = ref([])
const searchQuery = ref('')
const loading = ref(false)
const loadingMessages = ref(false)
const sending = ref(false)
const selectedFile = ref(null)
const showImageModal = ref(false)
const modalImage = ref('')
const messagesContainer = ref(null)
const messageContentRef = ref(null)
const messageUpdateTrigger = ref(0) 
const autoUpdateInterval = ref(null) 
const isReadingMessages = ref(false) 

const filteredConversations = computed(() => {
    if (!searchQuery.value) return conversations.value

    return conversations.value.filter((conversation) => {
        const user = conversation.user
        return (
            user.username?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            user.email?.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
    })
})

const computedMessages = computed(() => {
    return messages.value.map(m => ({
        content: m.message,
        isAdmin: m.sender_id === currentAdmin.value?.id,
        time: formatTime(m.sent_at),
        ...m
    }))
})

const adminAvatar = ref(
    'https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg'
)

const loadConversations = async () => {
    try {
        const newConversations = await getConversations()
        
        const hasChanges = JSON.stringify(newConversations) !== JSON.stringify(conversations.value)
        
        if (hasChanges) {
            conversations.value = newConversations
            console.log('[Admin] Conversations có thay đổi, count:', newConversations.length)
        }
    } catch (err) {
        console.error('Lỗi khi tải cuộc trò chuyện:', err)
    }
}

const selectConversation = async (conversation) => {
  selectedUser.value = conversation.user
  messages.value = [] 
  await loadMessages() 
  await nextTick()
  messageContentRef.value?.scrollToBottom()
  await loadConversations()
}

function isAtBottom() {
  if (!messagesContainer.value) return true
  const { scrollTop, scrollHeight, clientHeight } = messagesContainer.value
  return scrollHeight - scrollTop - clientHeight < 50 // 50px tolerance
}

const loadMessages = async () => {
  if (!selectedUser.value) return
  try {
    loadingMessages.value = true
    const newMessages = await getMessages(selectedUser.value.id)
    
    const hasNewMessages = JSON.stringify(newMessages) !== JSON.stringify(messages.value)
    
    if (hasNewMessages) {
      messages.value = newMessages
      console.log('[Admin] Có tin nhắn mới, count:', newMessages.length)
      await nextTick()
      if (isAtBottom()) scrollToBottom()
    }
  } catch (err) {
    console.error('Lỗi khi tải tin nhắn:', err)
  } finally {
    loadingMessages.value = false
  }
}

const handleSendMessage = async ({ text, file }) => {
    if ((!text.trim() && !file) || sending.value || !selectedUser.value) return
    try {
        sending.value = true
        const messageData = {
            receiver_id: selectedUser.value.id,
            message: text
        }
        if (file) messageData.attachment = file

        const message = await sendChatMessage(messageData)
        messages.value.push(message)
        await nextTick()
        scrollToBottom()
        await loadConversations()
        const chatChannel = new BroadcastChannel('chat_channel')
        chatChannel.postMessage({ type: 'new_message', userId: selectedUser.value.id })
    } catch (err) {
        console.error('Lỗi khi gửi tin nhắn:', err)
    } finally {
        sending.value = false
    }
}

const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
}

const getUserAvatar = (avatar) => {
    if (!avatar)
        return 'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'
    if (avatar.startsWith('http')) return avatar
    return `${apiBaseUrl}/${avatar.replace(/^\/+/, '')}`
}

const isImage = (filename) => /\.(jpg|jpeg|png|gif)$/i.test(filename)

const openImage = (src) => {
    modalImage.value = src
    showImageModal.value = true
}

const closeImageModal = () => {
    showImageModal.value = false
    modalImage.value = ''
}

const startAutoUpdate = () => {
    stopAutoUpdate() 
    autoUpdateInterval.value = setInterval(async () => {
        await loadConversations()
    }, 10000) 
}

const stopAutoUpdate = () => {
    if (autoUpdateInterval.value) {
        clearInterval(autoUpdateInterval.value)
        autoUpdateInterval.value = null
    }
}

watch(selectedUser, (val) => {
  if (val) {
    loadMessages()
  }
})

watch(conversations, (newConversations, oldConversations) => {
  if (
    selectedUser.value &&
    newConversations.length &&
    oldConversations &&
    newConversations[0].user.id === selectedUser.value.id &&
    JSON.stringify(newConversations[0].latest_message) !== JSON.stringify(oldConversations[0]?.latest_message)
  ) {
    console.log('[Admin] Sidebar có tin nhắn mới, đang mở đúng user, gọi loadMessages')
    loadMessages()
  }
})

const chatChannel = new BroadcastChannel('chat_channel')
chatChannel.onmessage = async (event) => {
  if (event.data.type === 'new_message') {
    console.log('[Admin] Nhận sự kiện new_message, gọi loadMessages', event.data)
    await loadMessages()
    await nextTick()
    if (messageContentRef.value) {
      messageContentRef.value.scrollToBottom()
    }
  }
}

watch(messages, (newMessages) => {
  nextTick(() => {
    if (messageContentRef.value) {
      messageContentRef.value.scrollToBottom()
    }
  })
}, { deep: true })

onMounted(async () => {
    console.log('[Admin] Trang tin nhắn được mở - Bắt đầu load dữ liệu')
    await loadConversations() // Load ngay lập tức
    startAutoUpdate()
})

onUnmounted(() => {
    stopAutoUpdate()
})
</script>


<style scoped>
.admin-chat-container {
    height: calc(100vh - 64px);
}

.chat-header {
    background-color: #3BB77E;
}

.btn-primary,
.bg-primary {
    background-color: #3BB77E !important;
    color: #fff !important;
    border: none;
}

.btn-primary:hover,
.bg-primary:hover {
    background-color: #339966 !important;
}

.border-primary {
    border-color: #3BB77E;
}

.message-admin {
    background-color: #3BB77E !important;
    color: #fff !important;
}

.focus\:ring-primary:focus {
    --ring-color: #3BB77E;
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #999;
}

.bg-primary {
    background-color: #3BB77E;
}

.border-primary {
    border-color: #3BB77E;
}

.text-primary {
    color: #3BB77E;
}
</style>