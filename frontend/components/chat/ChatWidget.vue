<template>
  <div v-if="isAuthenticated" class="tw-fixed tw-bottom-4 tw-right-4 tw-z-50">
    <!-- Chat Toggle Button -->
    <button
      v-if="!isOpen"
      @click="toggleChat"
      class="chat-button tw-text-white tw-rounded-full tw-w-16 tw-h-16 tw-flex tw-items-center tw-justify-center tw-shadow-lg hover:tw-opacity-90 tw-transition-all tw-relative"
    >
      <i class="fas fa-headset tw-text-xl"></i>
      <span v-if="unreadCount > 0" class="tw-absolute tw--top-2 tw--right-2 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-6 tw-h-6 tw-flex tw-items-center tw-justify-center tw-text-xs tw-font-bold">
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Box hỗ trợ -->
    <div v-if="!isOpen" class="support-hint tw-mb-2 tw-mr-2">
      <span>Bạn cần hỗ trợ gì?</span>
    </div>

    <!-- Chat Window -->
    <div
      v-if="isOpen"
      class="tw-bg-white tw-rounded-lg tw-shadow-2xl tw-w-96 tw-h-[500px] tw-flex tw-flex-col tw-overflow-hidden"
    >
      <!-- Header -->
      <div class="chat-header tw-text-white tw-p-4 tw-flex tw-justify-between tw-items-center">
        <div class="tw-flex tw-items-center tw-gap-3">
          <div class="tw-w-8 tw-h-8 tw-bg-white tw-bg-opacity-20 tw-rounded-full tw-flex tw-items-center tw-justify-center">
            <i class="fas fa-headset tw-text-sm"></i>
          </div>
          <div>
            <h3 class="tw-font-semibold">Hỗ trợ khách hàng</h3>
            <p class="tw-text-xs tw-opacity-90">Chat với admin</p>
          </div>
        </div>
        <button @click="toggleChat" class="tw-text-white hover:tw-text-gray-200 tw-transition-colors">
          <i class="fas fa-times tw-text-lg"></i>
        </button>
      </div>

      <!-- Admin Selection or Chat -->
      <div v-if="!currentAdmin" class="tw-flex-1 tw-overflow-hidden tw-flex tw-flex-col">
        <!-- Admin List -->
        <div class="tw-p-4">
          <div class="tw-text-center tw-mb-4">
            <i class="fas fa-user-tie tw-text-4xl tw-text-gray-400 tw-mb-2"></i>
            <h4 class="tw-font-medium tw-text-gray-700">Chọn admin để hỗ trợ</h4>
            <p class="tw-text-sm tw-text-gray-500">Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
          </div>

          <!-- Loading State -->
          <div v-if="loadingAdmins" class="tw-text-center tw-py-8">
            <i class="fas fa-spinner tw-animate-spin tw-text-2xl tw-text-gray-400 tw-mb-2"></i>
            <div class="tw-text-gray-500">Đang tải...</div>
          </div>

          <!-- Admin List -->
          <div v-else-if="admins.length > 0" class="tw-space-y-3">
            <div
              v-for="admin in admins"
              :key="admin.id"
              @click="selectAdmin(admin)"
              class="tw-flex tw-items-center tw-gap-3 tw-p-3 tw-rounded-lg tw-border tw-border-gray-200 hover:border-primary tw-cursor-pointer tw-transition-all hover:tw-shadow-sm"
            >
              <img
                :src="admin.avatar ? (admin.avatar.startsWith('http') ? admin.avatar : runtimeConfig.public.apiBaseUrl + '/storage/' + admin.avatar) : 'https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg'"
                :alt="admin.name"
                class="tw-w-12 tw-h-12 tw-rounded-full tw-object-cover tw-border-2 border-primary"
              >
              <div class="tw-flex-1">
                <div class="tw-font-medium tw-text-gray-800">{{ admin.name || admin.username }}</div>
                <div class="tw-text-sm tw-text-gray-500 tw-flex tw-items-center tw-gap-1">
                  <i class="fas fa-circle tw-text-green-400 tw-text-xs"></i>
                  Trực tuyến
                </div>
              </div>
              <i class="fas fa-chevron-right tw-text-gray-400"></i>
            </div>
          </div>

          <!-- No Admin State -->
          <div v-else class="tw-text-center tw-py-8 tw-text-gray-500">
            <i class="fas fa-exclamation-circle tw-text-3xl tw-mb-2"></i>
            <div>Hiện tại không có admin trực tuyến</div>
            <button @click="loadAdmins" class="tw-mt-2 btn-primary tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-text-sm">
              <i class="fas fa-refresh tw-mr-1"></i>Thử lại
            </button>
          </div>
        </div>
      </div>

      <!-- Chat Messages with Admin -->
      <div v-else class="tw-flex-1 tw-overflow-hidden tw-flex tw-flex-col">
        <!-- Chat Header -->
        <div class="tw-p-3 tw-border-b tw-flex tw-items-center tw-gap-3">
          <button @click="backToAdminList" class="tw-text-gray-600 hover:tw-text-gray-800 tw-transition-colors">
            <i class="fas fa-arrow-left"></i>
          </button>
          <img
            :src="currentAdmin.avatar ? (currentAdmin.avatar.startsWith('http') ? currentAdmin.avatar : runtimeConfig.public.apiBaseUrl + '/storage/' + currentAdmin.avatar) : 'https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg'"
            :alt="currentAdmin.name"
            class="tw-w-8 tw-h-8 tw-rounded-full tw-object-cover"
          >
          <div class="tw-flex-1">
            <div class="tw-font-medium tw-text-sm">{{ currentAdmin.name || currentAdmin.username }}</div>
            <div class="tw-text-xs tw-text-gray-500 tw-flex tw-items-center tw-gap-1">
              <i class="fas fa-circle tw-text-green-400 tw-text-xs"></i>
              Admin - Hỗ trợ khách hàng
            </div>
          </div>
        </div>

        <!-- Messages -->
        <div class="tw-flex-1 tw-overflow-y-auto tw-p-3 tw-space-y-3" ref="messagesContainer">
          <div v-if="messages.length === 0" class="tw-text-center tw-py-8 tw-text-gray-500">
            <i class="fas fa-comment-dots tw-text-3xl tw-mb-2"></i>
            <div class="tw-font-medium tw-mb-1">Chào mừng bạn đến với hỗ trợ khách hàng!</div>
            <div class="tw-text-sm">Hãy gửi tin nhắn để chúng tôi có thể hỗ trợ bạn</div>
          </div>
          <div
            v-for="message in messages"
            :key="message.id"
            :class="[
              'tw-flex',
              message.sender_id === user?.id ? 'tw-justify-end' : 'tw-justify-start'
            ]"
          >
            <div
              :class="[
                'tw-max-w-xs tw-p-3 tw-rounded-lg tw-relative tw-group',
                message.sender_id === user?.id
                  ? 'message-sent tw-text-white'
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

              <!-- Message Text -->
              <div>{{ message.message }}</div>

              <!-- Time -->
              <div
                :class="[
                  'tw-text-xs tw-mt-1',
                  message.sender_id === user?.id ? 'tw-text-blue-100' : 'tw-text-gray-500'
                ]"
              >
                {{ formatTime(message.sent_at) }}
                <i v-if="message.sender_id === user?.id && message.is_read" class="fas fa-check-double tw-ml-1"></i>
                <i v-else-if="message.sender_id === user?.id" class="fas fa-check tw-ml-1"></i>
              </div>

              <!-- Delete Button -->
              <button
                v-if="message.sender_id === user?.id"
                @click="deleteMessage(message.id)"
                class="tw-absolute tw--top-2 tw--right-2 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-5 tw-h-5 tw-flex tw-items-center tw-justify-center tw-text-xs tw-opacity-0 group-hover:tw-opacity-100 tw-transition-opacity"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Message Input -->
        <div class="tw-p-3 tw-border-t">
          <form @submit.prevent="sendMessage" class="tw-flex tw-gap-2">
            <div class="tw-flex-1 tw-relative">
              <input
                v-model="newMessage"
                type="text"
                placeholder="Nhập tin nhắn..."
                class="tw-w-full tw-pr-10 tw-pl-4 tw-py-2 tw-border tw-rounded-lg tw-text-sm focus:tw-outline-none focus:tw-ring-2 focus:ring-primary"
                :disabled="sending"
              >
              <label class="tw-absolute tw-right-3 tw-top-1/2 tw-transform tw--translate-y-1/2 tw-cursor-pointer tw-text-gray-400 hover:tw-text-gray-600">
                <i class="fas fa-paperclip"></i>
                <input
                  type="file"
                  ref="fileInput"
                  @change="handleFileSelect"
                  class="tw-hidden"
                  accept="image/*,.pdf,.doc,.docx"
                >
              </label>
            </div>
            <button
              type="submit"
              :disabled="(!newMessage.trim() && !selectedFile) || sending"
              class="btn-primary tw-text-white tw-px-4 tw-py-2 tw-rounded-lg tw-transition-all disabled:tw-opacity-50 disabled:tw-cursor-not-allowed"
            >
              <i v-if="sending" class="fas fa-spinner tw-animate-spin"></i>
              <i v-else class="fas fa-paper-plane"></i>
            </button>
          </form>
          
          <!-- Selected File Preview -->
          <div v-if="selectedFile" class="tw-mt-2 tw-flex tw-items-center tw-gap-2 tw-p-2 tw-bg-gray-100 tw-rounded">
            <i class="fas fa-file tw-text-gray-600"></i>
            <span class="tw-text-sm tw-flex-1">{{ selectedFile.name }}</span>
            <button @click="removeFile" class="tw-text-red-500 hover:tw-text-red-700">
              <i class="fas fa-times"></i>
            </button>
          </div>
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
import { ref, onMounted, nextTick, watch } from 'vue'
import { useChat } from '~/composables/useChat'
import { useAuth } from '~/composables/useAuth'

const runtimeConfig = useRuntimeConfig()
const { user, isAuthenticated } = useAuth()
const {
  getMessages,
  sendMessage: sendChatMessage,
  getUnreadCount,
  deleteMessage: deleteChatMessage,
  getAdmins
} = useChat()

// States
const isOpen = ref(false)
const admins = ref([])
const currentAdmin = ref(null)
const messages = ref([])
const newMessage = ref('')
const unreadCount = ref(0)
const sending = ref(false)
const selectedFile = ref(null)
const showImageModal = ref(false)
const modalImage = ref('')
const messagesContainer = ref(null)
const loadingAdmins = ref(false)

// Methods
const toggleChat = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value && isAuthenticated.value) {
    loadAdmins()
    loadUnreadCount()
  }
}

const loadAdmins = async () => {
  try {
    loadingAdmins.value = true
    admins.value = await getAdmins()
  } catch (error) {
    console.error('Lỗi khi tải danh sách admin:', error)
  } finally {
    loadingAdmins.value = false
  }
}

const selectAdmin = (admin) => {
  currentAdmin.value = admin
  loadMessages()
}

const backToAdminList = () => {
  currentAdmin.value = null
  messages.value = []
}

const loadUnreadCount = async () => {
  try {
    const result = await getUnreadCount()
    unreadCount.value = result.unread_count
  } catch (error) {
    console.error('Lỗi khi tải số tin nhắn chưa đọc:', error)
  }
}

const loadMessages = async () => {
  if (!currentAdmin.value) return
  
  try {
    messages.value = await getMessages(currentAdmin.value.id)
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Lỗi khi tải tin nhắn:', error)
  }
}

const sendMessage = async () => {
  if ((!newMessage.value.trim() && !selectedFile.value) || sending.value || !currentAdmin.value) return

  try {
    sending.value = true
    const messageData = {
      receiver_id: currentAdmin.value.id,
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
  } catch (error) {
    console.error('Lỗi khi gửi tin nhắn:', error)
  } finally {
    sending.value = false
  }
}

const deleteMessage = async (messageId) => {
  if (!confirm('Bạn có chắc chắn muốn xóa tin nhắn này?')) return

  try {
    await deleteChatMessage(messageId)
    const index = messages.value.findIndex(m => m.id === messageId)
    if (index !== -1) {
      messages.value.splice(index, 1)
    }
  } catch (error) {
    console.error('Lỗi khi xóa tin nhắn:', error)
  }
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
  if (diff < 3600000) return `${Math.floor(diff / 60000)} phút`
  if (diff < 86400000) return `${Math.floor(diff / 3600000)} giờ`
  return date.toLocaleDateString('vi-VN')
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

// Auto refresh every 30 seconds
const autoRefresh = () => {
  if (isAuthenticated.value) {
    loadUnreadCount()
    if (currentAdmin.value) {
      loadMessages()
    }
  }
}

onMounted(() => {
  // Auto refresh every 30 seconds
  setInterval(autoRefresh, 30000)
})

// Watch for authentication changes
watch(isAuthenticated, (newVal) => {
  if (newVal) {
    loadUnreadCount()
  } else {
    unreadCount.value = 0
    admins.value = []
    currentAdmin.value = null
    messages.value = []
    isOpen.value = false
  }
})
</script>

<style scoped>
.chat-button {
  background-color: #81AACC;
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

.message-sent {
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

.support-hint {
  position: absolute;
  right: 100%;
  bottom: 0;
  margin-right: 16px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  padding: 10px 18px;
  font-size: 15px;
  color: #333;
  z-index: 1001;
  white-space: nowrap;
}
</style> 