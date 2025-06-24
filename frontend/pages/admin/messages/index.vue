<template>
  <div class="admin-chat-container tw-h-full tw-flex tw-bg-gray-50">
    <!-- Sidebar - User Conversations -->
    <div class="tw-w-1/3 tw-bg-white tw-border-r tw-border-gray-200 tw-flex tw-flex-col">
      <!-- Header -->
      <div class="chat-header tw-text-white tw-p-4 tw-border-b">
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

      <!-- Search -->
      <div class="tw-p-4 tw-border-b tw-border-gray-100">
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

      <!-- Conversations List -->
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
                :src="conversation.user.avatar ? getUserAvatar(conversation.user.avatar) : '/images/default-avatar.png'"
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
    <div class="tw-flex-1 tw-flex tw-flex-col">
      <!-- No Selection State -->
      <div v-if="!selectedUser" class="tw-flex-1 tw-flex tw-items-center tw-justify-center tw-bg-white">
        <div class="tw-text-center tw-text-gray-500">
          <i class="fas fa-comment-dots tw-text-6xl tw-mb-4"></i>
          <h3 class="tw-text-xl tw-font-medium tw-mb-2">Chọn một cuộc trò chuyện</h3>
          <p class="tw-text-gray-400">Chọn khách hàng từ danh sách để bắt đầu chat</p>
        </div>
      </div>

      <!-- Chat Interface -->
      <div v-else class="tw-flex-1 tw-flex tw-flex-col tw-bg-white">
        <!-- Chat Header -->
        <div class="tw-p-4 tw-border-b tw-border-gray-200 tw-bg-white tw-shadow-sm">
          <div class="tw-flex tw-items-center tw-gap-3">
            <img
              :src="selectedUser.avatar ? getUserAvatar(selectedUser.avatar) : '/images/default-avatar.png'"
              :alt="selectedUser.username"
              class="tw-w-10 tw-h-10 tw-rounded-full tw-object-cover tw-border-2 tw-border-gray-200"
            >
            <div class="tw-flex-1">
              <div class="tw-font-medium tw-text-gray-900">{{ selectedUser.username || selectedUser.username }}</div>
              <div class="tw-text-sm tw-text-gray-500">{{ selectedUser.email }}</div>
            </div>
            <div class="tw-flex tw-gap-2">
              <button
                @click="refreshMessages"
                class="tw-p-2 tw-text-gray-400 hover:tw-text-gray-600 tw-transition-colors"
                title="Làm mới"
              >
                <i class="fas fa-refresh"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Messages -->
        <div class="tw-flex-1 tw-overflow-y-auto tw-p-4 tw-space-y-4" ref="messagesContainer">
          <!-- Loading Messages -->
          <div v-if="loadingMessages" class="tw-text-center tw-py-8">
            <i class="fas fa-spinner tw-animate-spin tw-text-xl tw-text-gray-400 tw-mb-2"></i>
            <div class="tw-text-gray-500">Đang tải tin nhắn...</div>
          </div>

          <!-- Messages List -->
          <div v-else-if="messages.length > 0" class="tw-space-y-4">
            <div
              v-for="message in messages"
              :key="message.id"
              :class="[
                'tw-flex tw-gap-3',
                message.sender_id === currentAdmin?.id ? 'tw-justify-end' : 'tw-justify-start'
              ]"
            >
              <div v-if="message.sender_id !== currentAdmin?.id" class="tw-flex-shrink-0">
                <img
                  :src="selectedUser.avatar ? getUserAvatar(selectedUser.avatar) : '/images/default-avatar.png'"
                  :alt="selectedUser.username"
                  class="tw-w-8 tw-h-8 tw-rounded-full tw-object-cover"
                >
              </div>

              <div
                :class="[
                  'tw-max-w-md tw-rounded-lg tw-p-3 tw-shadow-sm',
                  message.sender_id === currentAdmin?.id
                    ? 'message-admin tw-text-white'
                    : 'tw-bg-gray-100 tw-text-gray-900'
                ]"
              >
                <!-- Attachment -->
                <div v-if="message.attachment" class="tw-mb-2">
                  <img
                    v-if="isImage(message.attachment)"
                    :src="runtimeConfig.public.apiBaseUrl + '/storage/' + message.attachment"
                    class="tw-max-w-full tw-rounded tw-cursor-pointer"
                    @click="openImage(runtimeConfig.public.apiBaseUrl + '/storage/' + message.attachment)"
                  >
                  <a
                    v-else
                    :href="runtimeConfig.public.apiBaseUrl + '/storage/' + message.attachment"
                    target="_blank"
                    class="tw-flex tw-items-center tw-gap-2 tw-p-2 tw-bg-white tw-bg-opacity-20 tw-rounded"
                  >
                    <i class="fas fa-file"></i>
                    <span class="tw-text-sm">{{ getFileName(message.attachment) }}</span>
                  </a>
                </div>

                <!-- Message Content -->
                <div class="tw-mb-1">{{ message.message }}</div>

                <!-- Time and Status -->
                <div
                  :class="[
                    'tw-text-xs tw-flex tw-justify-between tw-items-center tw-mt-2',
                    message.sender_id === currentAdmin?.id ? 'tw-text-blue-100' : 'tw-text-gray-500'
                  ]"
                >
                  <span>{{ formatTime(message.sent_at) }}</span>
                  <div v-if="message.sender_id === currentAdmin?.id" class="tw-flex tw-items-center tw-gap-1">
                    <i v-if="message.is_read" class="fas fa-check-double" title="Đã đọc"></i>
                    <i v-else class="fas fa-check" title="Đã gửi"></i>
                  </div>
                </div>
              </div>

              <div v-if="message.sender_id === currentAdmin?.id" class="tw-flex-shrink-0">
                <img
                  :src="currentAdmin.avatar ? getUserAvatar(currentAdmin.avatar) : '/images/default-avatar.png'"
                  :alt="currentAdmin.username"
                  class="tw-w-8 tw-h-8 tw-rounded-full tw-object-cover"
                >
              </div>
            </div>
          </div>

          <!-- Empty Messages -->
          <div v-else class="tw-text-center tw-py-8 tw-text-gray-500">
            <i class="fas fa-comment tw-text-4xl tw-mb-3"></i>
            <div class="tw-font-medium tw-mb-1">Chưa có tin nhắn nào</div>
            <div class="tw-text-sm">Bắt đầu cuộc trò chuyện với khách hàng</div>
          </div>
        </div>

        <!-- Message Input -->
        <div class="tw-p-4 tw-border-t tw-border-gray-200 tw-bg-white">
          <form @submit.prevent="sendMessage" class="tw-flex tw-gap-3">
            <div class="tw-flex-1">
              <div class="tw-relative">
                <input
                  v-model="newMessage"
                  type="text"
                  placeholder="Nhập tin nhắn..."
                  class="tw-w-full tw-pr-12 tw-pl-4 tw-py-3 tw-border tw-border-gray-300 tw-rounded-lg focus:tw-outline-none focus:tw-ring-2 focus:ring-primary focus:tw-border-transparent"
                  :disabled="sending"
                >
                <label class="tw-absolute tw-right-3 tw-top-1/2 tw-transform tw--translate-y-1/2 tw-cursor-pointer tw-text-gray-400 hover:tw-text-gray-600">
                  <i class="fas fa-paperclip tw-text-lg"></i>
                  <input
                    type="file"
                    ref="fileInput"
                    @change="handleFileSelect"
                    class="tw-hidden"
                    accept="image/*,.pdf,.doc,.docx"
                  >
                </label>
              </div>

              <!-- Selected File Preview -->
              <div v-if="selectedFile" class="tw-mt-2 tw-flex tw-items-center tw-gap-2 tw-p-2 tw-bg-gray-100 tw-rounded">
                <i class="fas fa-file tw-text-gray-600"></i>
                <span class="tw-text-sm tw-flex-1">{{ selectedFile.name }}</span>
                <button @click="removeFile" class="tw-text-red-500 hover:tw-text-red-700">
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>

            <button
              type="submit"
              :disabled="(!newMessage.trim() && !selectedFile) || sending"
              class="btn-primary tw-text-white tw-px-6 tw-py-3 tw-rounded-lg tw-font-medium tw-transition-all disabled:tw-opacity-50 disabled:tw-cursor-not-allowed tw-flex tw-items-center tw-gap-2"
            >
              <i v-if="sending" class="fas fa-spinner tw-animate-spin"></i>
              <i v-else class="fas fa-paper-plane"></i>
              <span class="tw-hidden sm:tw-inline">Gửi</span>
            </button>
          </form>
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
import { ref, onMounted, computed, nextTick } from 'vue'
import { useAuth } from '~/composables/useAuth'
import { useChat } from '~/composables/useChat'

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

// Computed
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

// Methods
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
}

const loadMessages = async () => {
  if (!selectedUser.value) return

  try {
    loadingMessages.value = true
    messages.value = await getMessages(selectedUser.value.id)
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Lỗi khi tải tin nhắn:', error)
  } finally {
    loadingMessages.value = false
  }
}

const sendMessage = async () => {
  if ((!newMessage.value.trim() && !selectedFile.value) || sending.value || !selectedUser.value) return

  try {
    sending.value = true
    const messageData = {
      receiver_id: selectedUser.value.id,
      message: newMessage.value
    }

    if (selectedFile.value) {
      messageData.attachment = selectedFile.value
    }

    const message = await sendChatMessage(messageData)
    messages.value.push(message)
    newMessage.value = ''
    selectedFile.value = null

    await nextTick()
    scrollToBottom()

    // Update conversation list
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
  if (!avatar) return '/images/default-avatar.png'
  if (avatar.startsWith('http')) return avatar
  return runtimeConfig.public.apiBaseUrl + '/storage/' + avatar
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

// Auto refresh
const autoRefresh = () => {
  loadConversations()
  if (selectedUser.value) {
    loadMessages()
  }
}

// Lifecycle
onMounted(() => {
  loadConversations()
  
  // Auto refresh every 30 seconds
  setInterval(autoRefresh, 30000)
})
</script>

<style scoped>
.admin-chat-container {
  height: calc(100vh - 64px);
}

.chat-header {
  background-color: #81AACC;
}

.btn-primary {
  background-color: #81AACC;
}

.btn-primary:hover {
  background-color: #6d92b3;
}

.border-primary {
  border-color: #81AACC;
}

.message-admin {
  background-color: #81AACC;
}

.focus\:ring-primary:focus {
  --tw-ring-color: #81AACC;
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
</style>