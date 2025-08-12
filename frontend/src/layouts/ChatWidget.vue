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
               alt="Avatar"
               class="w-8 h-8 rounded-full object-cover border-2 border-white/50" />
          <div>
            <h3 class="font-semibold">Hỗ trợ khách hàng</h3>
            <p class="text-xs opacity-90">Chat với admin</p>
          </div>
        </div>
        <button @click="toggleChat" class="text-white hover:text-gray-200 transition-colors">
          <i class="fas fa-times text-lg"></i>
        </button>
      </div>

      <!-- Admin Selection or Chat -->
      <div v-if="!currentAdmin" class="flex-1 overflow-hidden flex flex-col">
        <!-- Admin List -->
        <div class="p-4">
          <div class="text-center mb-4">
            <i class="fas fa-user-tie text-4xl text-gray-400 mb-2"></i>
            <h4 class="font-medium text-gray-700">Chọn admin để hỗ trợ</h4>
            <p class="text-sm text-gray-500">Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
          </div>

          <!-- Loading State -->
          <div v-if="loadingAdmins" class="text-center py-8">
            <i class="fas fa-spinner animate-spin text-2xl text-gray-400 mb-2"></i>
            <div class="text-gray-500">Đang tải...</div>
          </div>

          <!-- Admin List -->
          <div v-else-if="admins.length > 0" class="space-y-3">
            <div v-for="admin in admins" :key="admin.id" @click="selectAdmin(admin)"
              class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-primary cursor-pointer transition-all hover:shadow-sm">
              <img src="https://cdn-img.upanhlaylink.com/img/image_202505261a100993dadd1e94d860ec123578e3cf.jpg"
                :alt="admin.name" class="w-12 h-12 rounded-full object-cover border-2 border-primary">
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
          <div v-else class="text-center py-8 text-gray-500">
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
              'max-w-xs p-3 rounded-lg relative group',
              message.sender_id === user?.id
                ? 'message-sent text-white'
                : 'bg-gray-100 text-gray-900'
            ]">
              <!-- Attachment -->
              <div v-if="message.attachment" class="mb-2">
                <img v-if="isImage(message.attachment)"
                  :src="apiBaseUrl + '/storage/' + message.attachment"
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
                'text-xs mt-1',
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
                class="w-full pr-10 pl-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#81aacc]"
                :disabled="sending">
              <label
                class="absolute right-3 top-1/2 transform -translate-y-1/2 cursor-pointer text-gray-400 hover:text-gray-600">
                <i class="fas fa-paperclip"></i>
                <input type="file" ref="fileInput" @change="handleFileSelect" class="hidden"
                  accept="image/*,.pdf,.doc,.docx">
              </label>
            </div>
            <button type="submit" :disabled="(!newMessage.trim() && !selectedFile) || sending"
              class="btn-primary text-white px-4 py-2 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
              <i v-if="sending" class="fas fa-spinner animate-spin"></i>
              <i v-else class="fas fa-paper-plane"></i>
            </button>
          </form>

          <!-- Selected File Preview -->
          <div v-if="selectedFile" class="mt-2 flex items-center gap-2 p-2 bg-gray-100 rounded">
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
    <div v-if="showImageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
      @click="closeImageModal">
      <img :src="modalImage" class="max-w-[90%] max-h-[90%] object-contain">
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, watch, onUnmounted } from 'vue'
import { useChat } from '../composable/useChat'

// Khai báo API base URL
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

const props = defineProps({
  isAuthenticated: { type: Boolean, default: false }
})

const {
  getMessages,
  sendMessage: sendChatMessage,
  getUnreadCount,
  deleteMessage: deleteChatMessage,
  getAdmins
} = useChat()

const user = JSON.parse(localStorage.getItem('user') || 'null')
// const isAuthenticated = !!localStorage.getItem('token') // Đã bỏ, dùng props

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
  if (isOpen.value && props.isAuthenticated) {
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
    // PHÁT SỰ KIỆN realtime cho admin
    const chatChannel = new BroadcastChannel('chat_channel')
    chatChannel.postMessage({ type: 'new_message', userId: currentAdmin.value.id })
    console.log('[User] Phát sự kiện:', { type: 'new_message', userId: currentAdmin.value.id })
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

// Toggle a global class to hide AI floating button when this widget is open
watch(isOpen, (open) => {
  const root = document.documentElement
  if (open) root.classList.add('chatwidget-open')
  else root.classList.remove('chatwidget-open')
})

onUnmounted(() => {
  stopPolling()
  document.documentElement.classList.remove('chatwidget-open')
})

// Lắng nghe BroadcastChannel để nhận tin nhắn mới từ admin
const chatChannel = new BroadcastChannel('chat_channel')
chatChannel.onmessage = (event) => {
  console.log('[User] Nhận sự kiện:', event.data, 'user:', user)
  if (event.data.type === 'new_message' && user && String(event.data.userId) === String(user.id)) {
    console.log('[User] Gọi loadMessages')
    loadMessages()
  }
}
</script>

<style scoped>
.chat-button {
  background-color: #81AACC;
  border: 2px solid #fff;
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
  position: absolute;
  right: 100%;
  bottom: 0;
  margin-right: 16px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 10px 18px;
  font-size: 15px;
  color: #333;
  z-index: 1001;
  white-space: nowrap;
}

/* Responsive (mobile) */
@media (max-width: 480px) {
  .chat-button { width: 56px; height: 56px; }
  .support-hint { padding: 8px 12px; font-size: 13px; }
  .chat-panel {
    width: calc(100vw - 20px);
    height: calc(100vh - 100px);
  }
  .chat-header { padding: 12px; }
  .chat-header h3 { font-size: 14px; }
  .messages-area { padding: 8px !important; }
  .mobile-space > * + * { margin-top: 8px !important; }
  .input-area { padding: 8px !important; }
}

</style>