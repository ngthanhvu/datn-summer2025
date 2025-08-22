<template>
  <div v-if="isAuthenticated" class="fixed bottom-4 right-4 z-50">
    <!-- Chat Toggle Button -->
    <button v-if="!isOpen" @click="toggleChat"
      class="chat-button text-white rounded-full w-16 h-16 flex items-center justify-center shadow-lg hover:opacity-90 transition-all relative">
      <i class="fas fa-headset text-xl"></i>
      <span v-if="unreadCount > 0"
        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold">
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Box hỗ trợ -->
    <div v-if="!isOpen" class="support-hint mb-2 mr-2">
      <span>Bạn cần hỗ trợ gì?</span>
    </div>

    <div v-if="isOpen" class="bg-white rounded-lg shadow-2xl w-96 h-[500px] flex flex-col overflow-hidden chat-panel">
      <!-- Header -->
      <div class="chat-header text-white p-4 flex justify-between items-center">
        <div class="flex items-center gap-3">
          <img src="https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg"
            alt="Avatar" class="w-8 h-8 rounded-full object-cover border-2 border-white/50" />
          <div>
            <h3 class="font-semibold">Hỗ trợ khách hàng</h3>
            <p class="text-xs opacity-90">Chat với admin</p>
          </div>
        </div>
        <button @click="toggleChat" class="close-btn" type="button" aria-label="Đóng chat">
          <i class="fas fa-times text-lg"></i>
        </button>
      </div>

      <!-- Admin Selection or Chat -->
      <div v-if="!currentAdmin" class="flex-1 overflow-hidden flex flex-col">
        <!-- Admin List -->
        <div class="p-4 flex-1 overflow-hidden flex flex-col">
          <div class="text-center mb-4 flex-shrink-0">
            <i class="fas fa-user-tie text-4xl text-gray-400 mb-2"></i>
            <h4 class="font-medium text-gray-700">Chọn admin để hỗ trợ</h4>
            <p class="text-sm text-gray-500">Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
          </div>

          <!-- Loading State -->
          <div v-if="loadingAdmins" class="text-center py-8 flex-shrink-0">
            <i class="fas fa-spinner animate-spin text-2xl text-gray-400 mb-2"></i>
            <div class="text-gray-500">Đang tải...</div>
          </div>

          <!-- Admin List with Scroll -->
          <div v-else-if="admins.length > 0" class="flex-1 overflow-y-auto space-y-3 pr-1">
            <div v-for="admin in admins" :key="admin.id" @click="selectAdmin(admin)"
              class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-primary cursor-pointer transition-all hover:shadow-sm admin-list-item">
              <img src="https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg"
                :alt="admin.name" class="w-12 h-12 rounded-full object-cover border-2 border-primary admin-avatar">
              <div class="flex-1">
                <div class="font-medium text-gray-800">{{ admin.name || admin.username }}</div>
                <div class="text-sm text-gray-500 flex items-center gap-1">
                  <i class="fas fa-circle text-green-400 text-xs"></i>
                  Trực tuyến
                </div>
              </div>
              <i class="fas fa-chevron-right text-gray-400"></i>
            </div>
          </div>

          <!-- No Admin State -->
          <div v-else class="text-center py-8 text-gray-500 flex-shrink-0">
            <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
            <div>Hiện tại không có admin trực tuyến</div>
            <button @click="loadAdmins" class="mt-2 btn-primary text-white px-4 py-2 rounded-lg text-sm">
              <i class="fas fa-refresh mr-1"></i>Thử lại
            </button>
          </div>
        </div>
      </div>

      <!-- Chat Messages with Admin -->
      <div v-else class="flex-1 overflow-hidden flex flex-col">
        <!-- Chat Header -->
        <div class="p-3 border-b border-gray-300 flex items-center gap-3">
          <button @click="backToAdminList" class="text-gray-600 hover:text-gray-800 transition-colors">
            <i class="fas fa-arrow-left"></i>
          </button>
          <img src="https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg"
            :alt="currentAdmin.name" class="w-8 h-8 rounded-full object-cover">
          <div class="flex-1">
            <div class="font-medium text-sm">{{ currentAdmin.name || currentAdmin.username }}</div>
            <div class="text-xs text-gray-500 flex items-center gap-1">
              <i class="fas fa-circle text-green-400 text-xs"></i>
              Admin - Hỗ trợ khách hàng
            </div>
          </div>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-3 space-y-3 messages-area mobile-space" ref="messagesContainer">
          <div v-if="messages.length === 0" class="text-center py-8 text-gray-500">
            <i class="fas fa-comment-dots text-3xl mb-2"></i>
            <div class="font-medium mb-1">Chào mừng bạn đến với hỗ trợ khách hàng!</div>
            <div class="text-sm">Hãy gửi tin nhắn để chúng tôi có thể hỗ trợ bạn</div>
          </div>
          <div v-for="message in messages" :key="message.id" :class="[
            'flex',
            message.sender_id === user?.id ? 'justify-end' : 'justify-start'
          ]">
            <div :class="[
              'message-bubble',
              message.sender_id === user?.id
                ? 'message-sent'
                : 'message-received'
            ]">
              <!-- Attachment -->
              <div v-if="message.attachment" class="file-attachment">
                <img v-if="isImage(message.attachment)" :src="apiBaseUrl + '/storage/' + message.attachment"
                  class="max-w-full rounded cursor-pointer"
                  @click="openImage(apiBaseUrl + '/storage/' + message.attachment)">
                <a v-else :href="apiBaseUrl + '/storage/' + message.attachment" target="_blank"
                  class="flex items-center gap-2 p-2 bg-white bg-opacity-20 rounded">
                  <i class="fas fa-file"></i>
                  <span class="text-sm">{{ getFileName(message.attachment) }}</span>
                </a>
              </div>

              <!-- Message Text -->
              <div>{{ message.message }}</div>

              <!-- Time -->
              <div :class="[
                'text-xs mt-2',
                message.sender_id === user?.id ? 'text-blue-100' : 'text-gray-500'
              ]">
                {{ formatTime(message.sent_at) }}
                <i v-if="message.sender_id === user?.id && message.is_read" class="fas fa-check-double ml-1"></i>
                <i v-else-if="message.sender_id === user?.id" class="fas fa-check ml-1"></i>
              </div>

              <!-- Delete Button -->
              <button v-if="message.sender_id === user?.id" @click="deleteMessage(message.id)"
                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Message Input -->
        <div class="p-3 border-t border-gray-300 input-area">
          <form @submit.prevent="sendMessage" class="flex gap-2">
            <div class="flex-1 relative">
              <input v-model="newMessage" type="text" placeholder="Nhập tin nhắn..."
                class="w-full pr-10 pl-4 py-2 message-input rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#81aacc]"
                :disabled="sending">
              <label
                class="absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600">
                <i class="fas fa-paperclip"></i>
                <input type="file" ref="fileInput" @change="handleFileSelect" class="hidden"
                  accept="image/*,.pdf,.doc,.docx">
              </label>
            </div>
            <button type="submit" :disabled="(!newMessage.trim() && !selectedFile) || sending"
              class="send-btn transition-all disabled:opacity-50 disabled:cursor-not-allowed">
              <i v-if="sending" class="fas fa-spinner animate-spin"></i>
              <i v-else class="fas fa-paper-plane"></i>
            </button>
          </form>

          <!-- Selected File Preview -->
          <div v-if="selectedFile" class="mt-2 flex items-center gap-2 p-2 bg-gray-100 rounded file-attachment">
            <i class="fas fa-file text-gray-600"></i>
            <span class="text-sm flex-1">{{ selectedFile.name }}</span>
            <button @click="removeFile" class="text-red-500 hover:text-red-700">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Image Modal -->
    <div v-if="showImageModal" class="fixed inset-0 image-modal flex items-center justify-center z-50"
      @click="closeImageModal">
      <img :src="modalImage" class="max-w-[90%] max-h-[90%] object-contain">
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, watch, onUnmounted, computed } from 'vue'
import { useChat } from '../composable/useChat'

// Khai báo API base URL
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

const props = defineProps({
  isAuthenticated: { type: Boolean, default: false }
})

// Sử dụng props nếu được truyền, nếu không thì tự kiểm tra
const isAuthenticated = computed(() => {
  // Nếu có props được truyền, sử dụng props
  if (props.isAuthenticated !== undefined) {
    return props.isAuthenticated
  }

  // Nếu không có props, tự kiểm tra từ localStorage và cookies
  const localToken = localStorage.getItem('token')
  const cookieToken = document.cookie.includes('token=')
  const hasUser = !!user
  const authenticated = (!!localToken || cookieToken) && hasUser
  return authenticated
})

const {
  getMessages,
  sendMessage: sendChatMessage,
  getUnreadCount,
  deleteMessage: deleteChatMessage,
  getAdmins
} = useChat()

const user = JSON.parse(localStorage.getItem('user') || 'null')

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
const fileInput = ref(null)
const loadingAdmins = ref(false)
const pollingInterval = ref(null)

const toggleChat = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value && isAuthenticated.value) {
    loadAdmins()
    loadUnreadCount()
    document.documentElement.classList.add('chatwidget-open')
  } else {
    document.documentElement.classList.remove('chatwidget-open')
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

const startPolling = () => {
  stopPolling()
  pollingInterval.value = setInterval(() => {
    if (currentAdmin.value && isOpen.value) loadMessages()
  }, 3000)
}

const stopPolling = () => {
  if (pollingInterval.value) clearInterval(pollingInterval.value)
  pollingInterval.value = null
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
    await loadMessages()
    const chatChannel = new BroadcastChannel('chat_channel')
    chatChannel.postMessage({ type: 'new_message', userId: currentAdmin.value.id })
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
    const index = messages.value.findIndex((m) => m.id === messageId)
    if (index !== -1) messages.value.splice(index, 1)
  } catch (error) {
    console.error('Lỗi khi xóa tin nhắn:', error)
  }
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) selectedFile.value = file
}

const removeFile = () => {
  selectedFile.value = null
  if (fileInput.value) fileInput.value.value = ''
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

const isImage = (filename) => /\.(jpg|jpeg|png|gif)$/i.test(filename)

const getFileName = (path) => path.split('/').pop()

const openImage = (src) => {
  modalImage.value = src
  showImageModal.value = true
}

const closeImageModal = () => {
  showImageModal.value = false
  modalImage.value = ''
}

watch([isOpen, currentAdmin], ([open, admin]) => {
  if (open && admin) {
    loadMessages()
    startPolling()
  } else {
    stopPolling()
  }
})

watch(isOpen, (open) => {
  const root = document.documentElement
  if (open) {
    root.classList.add('chatwidget-open')
    root.classList.remove('ai-chatbot-open')
  } else {
    root.classList.remove('chatwidget-open')
  }
})

watch(isAuthenticated, (authenticated) => {
  if (!authenticated && isOpen.value) {
    isOpen.value = false
    document.documentElement.classList.remove('chatwidget-open')
  }
})

watch(() => props.isAuthenticated, (newValue) => {
}, { immediate: true })

onUnmounted(() => {
  stopPolling()
  document.documentElement.classList.remove('chatwidget-open')
})

const chatChannel = new BroadcastChannel('chat_channel')
chatChannel.onmessage = (event) => {
  if (event.data.type === 'new_message' && user && String(event.data.userId) === String(user.id)) {
    loadMessages()
  }
}
</script>

<style scoped>
.chat-button {
  background-color: #81AACC;
  border: 2px solid #fff;
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
  transition: all 0.3s ease;
  display: flex !important;
  opacity: 1 !important;
  visibility: visible !important;
}

.chat-button:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(129, 170, 204, 0.4);
}

.chat-header {
  background-color: #81AACC;
}

.btn-primary {
  background-color: #81AACC;
  border: none;
  color: white;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background-color: #6d92b3;
  transform: translateY(-1px);
}

.border-primary {
  border-color: #81AACC;
}

.message-sent {
  background-color: #81AACC;
  color: white;
  border-radius: 16px;
  padding: 10px 12px;
  margin-bottom: 8px;
  word-wrap: break-word;
}

.focus\:ring-primary:focus {
  --ring-color: #81AACC;
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

.support-hint {
  position: fixed;
  right: 80px;
  bottom: 20px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 10px 18px;
  font-size: 15px;
  color: #333;
  z-index: 1001;
  white-space: nowrap;
  border: 1px solid rgba(129, 170, 204, 0.2);
  display: block !important;
  opacity: 1 !important;
  visibility: visible !important;
}

.chat-panel {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(129, 170, 204, 0.2);
}

/* Message styling */
.message-bubble {
  max-width: 85%;
  padding: 12px 16px;
  border-radius: 18px;
  margin-bottom: 8px;
  word-wrap: break-word;
  line-height: 1.4;
}

.message-sent {
  background-color: #81AACC;
  color: white;
  margin-left: auto;
}

.message-received {
  background-color: #f1f5f9;
  color: #1e293b;
}

/* Input styling */
.message-input {
  border: 2px solid #e2e8f0;
  border-radius: 24px;
  padding: 12px 16px;
  font-size: 14px;
  transition: all 0.3s ease;
}

.message-input:focus {
  border-color: #81AACC;
  box-shadow: 0 0 0 3px rgba(129, 170, 204, 0.1);
  outline: none;
}

/* Send button */
.send-btn {
  background-color: #81AACC;
  color: white;
  border: none;
  border-radius: 50%;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  cursor: pointer;
}

.send-btn:hover:not(:disabled) {
  background-color: #6d92b3;
  transform: scale(1.05);
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* File attachment */
.file-attachment {
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 12px;
  margin-top: 8px;
}

/* Image modal */
.image-modal {
  background-color: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(4px);
}

.image-modal img {
  border-radius: 8px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

/* Responsive (mobile) */
@media (max-width: 480px) {
  .chat-button {
    width: 56px;
    height: 56px;
    bottom: 20px;
    right: 20px;
  }

  .support-hint {
    padding: 8px 12px;
    font-size: 13px;
    right: 80px;
    bottom: 20px;
    margin-right: 0;
  }

  .chat-panel {
    width: calc(100vw - 20px);
    height: calc(100vh - 100px);
    position: fixed;
    top: 10px;
    left: 10px;
    right: 10px;
    bottom: 10px;
    max-width: 100vw;
    max-height: 100vh;
    overflow: hidden;
    border-radius: 16px;
  }

  .chat-header {
    padding: 16px 12px;
    border-radius: 16px 16px 0 0;
  }

  .chat-header h3 {
    font-size: 16px;
  }

  .chat-header p {
    font-size: 11px;
  }

  .messages-area {
    padding: 12px 8px !important;
    flex: 1;
    overflow-y: auto;
  }

  .mobile-space>*+* {
    margin-top: 8px !important;
  }

  .input-area {
    padding: 12px 8px !important;
    border-radius: 0 0 16px 16px;
  }

  .input-container {
    gap: 8px;
  }

  .message-input {
    padding: 10px 14px;
    font-size: 13px;
    border-radius: 20px;
  }

  .send-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
  }

  /* Admin list mobile improvements */
  .admin-list-item {
    padding: 12px 8px;
    margin-bottom: 8px;
  }

  .admin-avatar {
    width: 40px;
    height: 40px;
  }

  /* Message bubbles mobile */
  .message-bubble {
    max-width: 85%;
    padding: 10px 12px;
    border-radius: 16px;
    font-size: 13px;
    line-height: 1.4;
  }

  /* File attachment mobile */
  .file-attachment {
    max-width: 100%;
    padding: 8px;
  }

  /* Image modal mobile */
  .image-modal img {
    max-width: 95%;
    max-height: 80vh;
  }

  /* Mobile close button improvements */
  .close-btn {
    min-width: 44px;
    min-height: 44px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
  }

  .close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
  }

  .close-btn i {
    font-size: 16px;
  }
}

/* Extra small screens */
@media (max-width: 360px) {
  .chat-panel {
    width: calc(100vw - 16px);
    height: calc(100vh - 80px);
    top: 8px;
    left: 8px;
    right: 8px;
    bottom: 8px;
  }

  .chat-button {
    width: 48px;
    height: 48px;
    bottom: 16px;
    right: 16px;
  }

  .support-hint {
    right: 70px;
    bottom: 16px;
    font-size: 12px;
    padding: 6px 10px;
  }

  .chat-header {
    padding: 12px 8px;
  }

  .chat-header h3 {
    font-size: 14px;
  }

  .messages-area {
    padding: 8px 6px !important;
  }

  .input-area {
    padding: 8px 6px !important;
  }
}

/* Landscape mobile */
@media (max-width: 480px) and (orientation: landscape) {
  .chat-panel {
    height: calc(100vh - 40px);
    top: 20px;
    bottom: 20px;
  }

  .chat-header {
    padding: 12px 16px;
  }

  .messages-area {
    padding: 8px 12px !important;
  }

  .input-area {
    padding: 8px 12px !important;
  }
}

/* High DPI screens */
@media (-webkit-min-device-pixel-ratio: 2),
(min-resolution: 192dpi) {
  .chat-button {
    border-width: 1px;
  }

  .chat-panel {
    border-width: 1px;
  }
}

.close-btn {
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  cursor: pointer;
  padding: 10px;
  border-radius: 50%;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  position: relative;
  z-index: 10;
  min-width: 40px;
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  outline: none;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.close-btn:active {
  transform: scale(0.95);
}

.close-btn:focus {
  outline: 2px solid rgba(255, 255, 255, 0.5);
  outline-offset: 2px;
}

/* Hide ChatWidget when AIChatbot is open */
:root.ai-chatbot-open .chat-button {
  display: none;
}

:root.ai-chatbot-open .support-hint {
  display: none;
}

:root.ai-chatbot-open .chat-panel {
  display: none;
}

/* Ensure chat button is always visible when not in ai-chatbot-open state */
.chat-button {
  display: flex !important;
}

/* Ensure support hint is always visible when not in ai-chatbot-open state */
.support-hint {
  display: block !important;
}
</style>