<template>
  <div class="admin-chat-container tw-h-screen tw-flex tw-bg-gray-50">
    <!-- Sidebar - User Conversations -->
    <div class="tw-w-1/3 tw-bg-white tw-border-r tw-border-gray-200 tw-flex tw-flex-col tw-h-[calc(100vh-64px)]">
      <!-- Header -->
      <div class="tw-p-4 tw-border-b" style="background-color: #3BB77E; color: #fff;">
        <div class="tw-flex tw-items-center tw-gap-3">
          <div class="tw-w-10 tw-h-10 tw-bg-white tw-bg-opacity-20 tw-rounded-full tw-flex tw-items-center tw-justify-center">
            <i class="fas fa-comments tw-text-lg"></i>
          </div>
          <div>
            <h2 class="tw-text-lg tw-font-semibold">Tin nhắn từ khách hàng</h2>
            <p class="tw-text-sm tw-opacity-90">{{ conversations.length }} cuộc trò chuyện</p>
          </div>
        </div>
      </div>

      <!-- Search (sticky) -->
      <div class="tw-p-4 tw-border-b tw-border-gray-100 tw-sticky tw-top-[64px] tw-z-10 tw-bg-white">
        <div class="tw-relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Tìm kiếm khách hàng..."
            class="tw-w-full tw-pl-10 tw-pr-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:ring-primary"
          >
          <i class="fas fa-search tw-absolute tw-left-3 tw-top-1/2 tw-transform tw--translate-y-1/2 tw-text-gray-400"></i>
        </div>
      </div>

      <!-- Conversations List (scrollable) -->
      <div class="tw-flex-1 tw-overflow-y-auto">
        <!-- Loading State -->
        <div v-if="loading" class="tw-p-4 tw-text-center">
          <i class="fas fa-spinner tw-animate-spin tw-text-xl tw-text-gray-400 tw-mb-2"></i>
          <div class="tw-text-gray-500">Đang tải cuộc trò chuyện...</div>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredConversations.length === 0" class="tw-p-8 tw-text-center tw-text-gray-500">
          <i class="fas fa-comment-slash tw-text-4xl tw-mb-3"></i>
          <div class="tw-font-medium tw-mb-1">Chưa có tin nhắn nào</div>
          <div class="tw-text-sm">Tin nhắn từ khách hàng sẽ hiển thị ở đây</div>
        </div>

        <!-- Conversations -->
        <div v-else class="tw-divide-y tw-divide-gray-100">
          <div
            v-for="conversation in filteredConversations"
            :key="conversation.user.id"
            @click="selectConversation(conversation)"
            :class="[
              'tw-flex tw-items-center tw-gap-3 tw-p-4 tw-cursor-pointer tw-transition-colors hover:tw-bg-gray-50',
              selectedUser?.id === conversation.user.id ? 'tw-bg-blue-50 tw-border-r-4 border-primary' : ''
            ]"
          >
            <div class="tw-relative">
              <img
                :src="conversation.user.avatar ? getUserAvatar(conversation.user.avatar) : 'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'"
                :alt="conversation.user.username"
                class="tw-w-12 tw-h-12 tw-rounded-full tw-object-cover tw-border-2 tw-border-gray-200"
              >
              <div v-if="conversation.unread_count > 0" class="tw-absolute tw--top-1 tw--right-1 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-text-xs tw-font-bold">
                {{ conversation.unread_count > 9 ? '9+' : conversation.unread_count }}
              </div>
            </div>
            <div class="tw-flex-1 tw-min-w-0">
              <div class="tw-flex tw-justify-between tw-items-start">
                <div class="tw-font-medium tw-text-gray-900 tw-truncate">
                  {{ conversation.user.username || conversation.user.username }}
                </div>
                <div class="tw-text-xs tw-text-gray-500 tw-ml-2">
                  {{ formatTime(conversation.latest_message.sent_at) }}
                </div>
              </div>
              <div class="tw-text-sm tw-text-gray-600 tw-truncate tw-mt-1">
                {{ conversation.latest_message.message }}
              </div>
              <div class="tw-text-xs tw-text-gray-400 tw-mt-1">
                {{ conversation.user.email }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Chat Area -->
    <div class="tw-flex-1 tw-w-0 tw-flex tw-flex-col tw-h-full tw-overflow-x-hidden">
      <MessageContent
        v-if="selectedUser"
        ref="messageContentRef"
        :message="{ name: selectedUser.username, email: selectedUser.email, avatar: getUserAvatar(selectedUser.avatar), messages: messages.map(m => ({ content: m.message, isAdmin: m.sender_id === currentAdmin?.id, time: formatTime(m.sent_at), ...m })) }"
        :adminAvatar="adminAvatar"
        @send="handleSendMessage"
      />
      <div v-else class="tw-flex-1 tw-flex tw-items-center tw-justify-center tw-bg-white">
        <div class="tw-text-center tw-text-gray-500">
          <i class="fas fa-comment-dots tw-text-6xl tw-mb-4"></i>
          <h3 class="tw-text-xl tw-font-medium tw-mb-2">Chọn một cuộc trò chuyện</h3>
          <p class="tw-text-gray-400">Chọn khách hàng từ danh sách để bắt đầu chat</p>
        </div>
      </div>
    </div>

    <!-- Image Modal -->
    <div
      v-if="showImageModal"
      class="tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-75 tw-flex tw-items-center tw-justify-center tw-z-50"
      @click="closeImageModal"
    >
      <img :src="modalImage" class="tw-max-w-[90%] tw-max-h-[90%] tw-object-contain">
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick, watch, onUnmounted } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useChat } from '~/composables/useChat'
import MessageContent from '~/components/admin/messages/MessageContent.vue'

const runtimeConfig = useRuntimeConfig()

// Page meta
useHead({
  title: 'Quản lý tin nhắn - Admin'
})

definePageMeta({
  layout: 'admin',
  middleware: 'admin'
})

// Auth & Chat composables
const { user: currentAdmin } = useAuth()
const {
  getConversations,
  getMessages,
  sendMessage: sendChatMessage
} = useChat()

// States
const conversations = ref([])
const selectedUser = ref(null)
const messages = ref([])
const newMessage = ref('')
const searchQuery = ref('')
const loading = ref(false)
const loadingMessages = ref(false)
const sending = ref(false)
const selectedFile = ref(null)
const showImageModal = ref(false)
const modalImage = ref('')
const messagesContainer = ref(null)
const pollingInterval = ref(null)
const messageContentRef = ref(null)

const filteredConversations = computed(() => {
  if (!searchQuery.value) return conversations.value

  return conversations.value.filter(conversation => {
    const user = conversation.user
    return (
      user.username?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email?.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  })
})

const adminAvatar = computed(() => currentAdmin.value?.avatar || 'https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg')

const loadConversations = async () => {
  try {
    loading.value = true
    conversations.value = await getConversations()
  } catch (error) {
    console.error('Lỗi khi tải cuộc trò chuyện:', error)
  } finally {
    loading.value = false
  }
}

const selectConversation = async (conversation) => {
  selectedUser.value = conversation.user
  await loadMessages()
  await nextTick()
  messageContentRef.value?.scrollToBottom()
}

const loadMessages = async () => {
  if (!selectedUser.value) return
  const container = messagesContainer.value
  const prevScrollHeight = container ? container.scrollHeight : 0
  const prevScrollTop = container ? container.scrollTop : 0
  const prevLastId = messages.value.length ? messages.value[messages.value.length - 1].id : null

  try {
    loadingMessages.value = true
    const newMessages = await getMessages(selectedUser.value.id)
    messages.value = newMessages
    await nextTick()
    const newLastId = messages.value.length ? messages.value[messages.value.length - 1].id : null
    if (newLastId !== prevLastId) {
      if (container) container.scrollTop = container.scrollHeight
    } else {
      if (container) container.scrollTop = prevScrollTop + (container.scrollHeight - prevScrollHeight)
    }
  } catch (error) {
    console.error('Lỗi khi tải tin nhắn:', error)
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
    if (file) {
      messageData.attachment = file
    }
    const message = await sendChatMessage(messageData)
    messages.value.push(message)
    await nextTick()
    scrollToBottom()
    await loadConversations()
  } catch (error) {
    console.error('Lỗi khi gửi tin nhắn:', error)
  } finally {
    sending.value = false
  }
}

const refreshMessages = async () => {
  await loadMessages()
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
  }
}

const removeFile = () => {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diff = now - date

  if (diff < 60000) return 'Vừa xong'
  if (diff < 3600000) return `${Math.floor(diff / 60000)} phút trước`
  if (diff < 86400000) return `${Math.floor(diff / 3600000)} giờ trước`
  if (diff < 604800000) return `${Math.floor(diff / 86400000)} ngày trước`
  return date.toLocaleDateString('vi-VN')
}

const getUserAvatar = (avatar) => {
  if (!avatar) return 'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'
  if (avatar.startsWith('http')) return avatar
  let url = runtimeConfig.public.apiBaseUrl + '/' + avatar.replace(/^\/+/, '')
  url = url.replace(/\/{2,}storage\//, '/storage/')
  return url
}

const isImage = (filename) => {
  return /\.(jpg|jpeg|png|gif)$/i.test(filename)
}

const getFileName = (path) => {
  return path.split('/').pop()
}

const openImage = (src) => {
  modalImage.value = src
  showImageModal.value = true
}

const closeImageModal = () => {
  showImageModal.value = false
  modalImage.value = ''
}

const startPolling = () => {
  stopPolling()
  pollingInterval.value = setInterval(() => {
    if (selectedUser.value) loadMessages()
  }, 2000) 
}

const stopPolling = () => {
  if (pollingInterval.value) clearInterval(pollingInterval.value)
  pollingInterval.value = null
}

onMounted(() => {
  loadConversations()
})

watch(selectedUser, (val) => {
  if (val) {
    loadMessages()
    startPolling()
  } else {
    stopPolling()
  }
})

onUnmounted(() => {
  stopPolling()
})
</script>

<style scoped>
.admin-chat-container {
  height: calc(100vh - 64px);
}

.chat-header {
  background-color: #3BB77E;
}

.btn-primary, .tw-bg-primary {
  background-color: #3BB77E !important;
  color: #fff !important;
  border: none;
}
.btn-primary:hover, .tw-bg-primary:hover {
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
  --tw-ring-color: #3BB77E;
}

.tw-animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
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

.tw-bg-primary {
  background-color: #3BB77E;
}
.tw-border-primary {
  border-color: #3BB77E;
}
.tw-text-primary {
  color: #3BB77E;
}
</style>