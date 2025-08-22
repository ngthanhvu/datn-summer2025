<template>
    <div class="messages-content flex-1 bg-gray-50 flex flex-col h-full">
        <div v-if="message" class="h-full flex flex-col min-h-0">
            <!-- Message Header (sticky) -->
            <div class="bg-white px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-300 sticky top-0 z-10">
                <div class="flex items-center gap-3 sm:gap-4">
                    <img :src="getAvatarUrl(message.avatar)" :alt="message.name"
                        class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover">
                    <div>
                        <h3 class="font-semibold text-sm sm:text-base">{{ message.name }}</h3>
                        <p class="text-xs sm:text-sm text-gray-500">{{ message.email }}</p>
                    </div>
                </div>
            </div>

            <!-- Messages (scrollable) -->
            <div class="flex-1 overflow-y-auto p-3 sm:p-6" ref="messagesContainer">
                <div class="max-w-3xl mx-auto space-y-3 sm:space-y-4">
                    <div v-for="(msg, index) in message.messages" :key="index" class="flex"
                        :class="[msg.isAdmin ? 'justify-end' : 'justify-start']">
                        <div class="flex items-end gap-2" :class="[msg.isAdmin ? 'flex-row-reverse' : '']">
                            <img :src="msg.isAdmin ? getAvatarUrl(adminAvatar) : getAvatarUrl(message.avatar)"
                                :alt="msg.isAdmin ? 'Admin' : message.name"
                                class="w-6 h-6 sm:w-8 sm:h-8 rounded-full object-cover">
                            <div :class="[
                                'max-w-xs sm:max-w-md rounded-2xl px-3 sm:px-4 py-2',
                                msg.isAdmin ? 'bg-primary text-white rounded-br-none' : 'bg-white rounded-bl-none shadow-sm'
                            ]">
                                <div v-if="msg.attachment" class="mb-2">
                                    <div v-if="/\.(jpg|jpeg|png|gif)$/i.test(msg.attachment)" class="relative">
                                        <img :src="getAvatarUrl(msg.attachment)" :alt="'Attachment'"
                                            class="max-w-full sm:max-w-xs rounded cursor-pointer block"
                                            @click="openImage(getAvatarUrl(msg.attachment))" @error="handleImageError"
                                            loading="lazy">
                                        <div v-if="!getAvatarUrl(msg.attachment)"
                                            class="max-w-full sm:max-w-xs h-32 bg-gray-200 rounded flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400 text-2xl"></i>
                                        </div>
                                    </div>
                                    <a v-else :href="getAvatarUrl(msg.attachment)" target="_blank"
                                        class="flex items-center gap-2 p-2 bg-white bg-opacity-20 rounded text-xs sm:text-sm">
                                        <i class="fas fa-file"></i>
                                        <span>{{ getFileName(msg.attachment) }}</span>
                                    </a>
                                </div>
                                <p v-if="msg.content" class="text-sm sm:text-base">{{ msg.content }}</p>
                                <span class="text-xs opacity-75 block mt-1">{{ msg.time }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Input (sticky) -->
            <div class="bg-white px-3 sm:px-6 py-3 sm:py-4 border-t border-gray-300 mt-auto flex-shrink-0"
                style="min-height: 80px; background-color: white !important; position: relative; z-index: 10;">
                <div class="max-w-3xl mx-auto">
                    <!-- File preview (if selected) -->
                    <div v-if="selectedFile" class="mb-3 flex items-center gap-2 p-2 bg-gray-100 rounded text-sm">
                        <i class="fas fa-file text-gray-600"></i>
                        <span class="flex-1 truncate">{{ selectedFile.name }}</span>
                        <button @click="removeFile" class="text-red-500 hover:text-red-700 p-1">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Debug: Always visible input area -->
                    <div class="flex gap-2 sm:gap-4 items-center">
                        <input type="text" v-model="newMessage" @keyup.enter="handleSend" placeholder="Nhập tin nhắn..."
                            class="flex-1 border border-gray-300 rounded-full px-4 sm:px-6 py-2 sm:py-3 text-sm sm:text-base focus:ring-1 focus:ring-[#3BB77E] focus:border-[#3BB77E] focus:outline-none"
                            style="min-height: 40px;" :disabled="false">
                        <label
                            class="bg-gray-100 rounded-full w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center cursor-pointer hover:bg-gray-200 flex-shrink-0 transition-colors">
                            <i class="fas fa-paperclip text-sm sm:text-lg text-gray-600"></i>
                            <input type="file" ref="fileInput" @change="handleFileSelect" class="hidden"
                                accept="image/*,.pdf,.doc,.docx">
                        </label>
                        <button @click="handleSend" :disabled="(!newMessage.trim() && !selectedFile)"
                            class="bg-primary text-white rounded-full w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0 transition-colors">
                            <i class="fas fa-paper-plane text-sm sm:text-base"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="h-full flex items-center justify-center text-gray-500 p-4">
            <div class="text-center">
                <i class="fas fa-comments text-3xl sm:text-4xl mb-2"></i>
                <p class="text-sm sm:text-base">Chọn một cuộc trò chuyện để bắt đầu</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL

const defaultAvatar =
    'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'

const props = defineProps({
    message: {
        type: Object,
        default: null
    },
    adminAvatar: {
        type: String,
        required: true
    }
})

const emit = defineEmits(['send'])

const newMessage = ref('')
const selectedFile = ref(null)
const fileInput = ref(null)

const handleSend = () => {
    if (!newMessage.value.trim() && !selectedFile.value) return
    emit('send', { text: newMessage.value, file: selectedFile.value })
    newMessage.value = ''
    selectedFile.value = null
    if (fileInput.value) fileInput.value.value = ''
}

const handleFileSelect = (event) => {
    const file = event.target.files[0]
    if (file) selectedFile.value = file
}

const removeFile = () => {
    selectedFile.value = null
    if (fileInput.value) fileInput.value.value = ''
}

const messagesContainer = ref(null)

const scrollToBottom = () => {
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
}

watch(
    () => props.message?.messages,
    async () => {
        await nextTick()
        scrollToBottom()
    },
    { deep: true }
)

onMounted(() => {
    scrollToBottom()
})

const getAvatarUrl = (avatar) => {
    if (!avatar) {
        return defaultAvatar
    }

    if (avatar.startsWith('http')) {
        return avatar
    }

    let cleanPath = avatar

    cleanPath = cleanPath.replace(/^\/+/, '')

    if (!cleanPath.startsWith('storage/')) {
        cleanPath = `storage/${cleanPath}`
    }

    let url = `${apiBaseUrl}/${cleanPath}`

    url = url.replace(/([^:]\/)\/+/g, '$1')

    return url
}

const handleImageError = (event) => {
    console.error('Image failed to load:', event.target.src)
    event.target.src = defaultAvatar
}

const getFileName = (path) => {
    if (!path) return ''
    return path.split('/').pop() || path
}

const openImage = (src) => {
    if (src) window.open(src, '_blank')
}

defineExpose({ scrollToBottom })
</script>

<style scoped>
.messages-content {
    height: 100%;
    max-height: 100%;
    display: flex;
    flex-direction: column;
}

.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
}

/* Ensure proper flex layout */
.flex-1 {
    flex: 1;
    min-height: 0;
}

.flex-shrink-0 {
    flex-shrink: 0;
}

/* Image loading styles */
img {
    transition: opacity 0.3s ease;
}

img[src=""],
img:not([src]) {
    opacity: 0;
}

/* Scrollbar styles */
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

/* Ensure input is always visible */
.mt-auto {
    margin-top: auto;
}

/* Debug styles */
.border-t {
    border-top: 1px solid #e5e7eb !important;
}

/* Mobile responsive adjustments */
@media (max-width: 1024px) {
    .messages-content {
        height: 100%;
        min-height: 100%;
        display: flex !important;
        flex-direction: column !important;
    }

    /* Ensure input is visible on mobile */
    .mt-auto {
        margin-top: auto !important;
        position: relative !important;
        z-index: 20 !important;
        flex-shrink: 0 !important;
        background: white !important;
        border-top: 2px solid #3BB77E !important;
    }

    /* Make sure the input area has enough space */
    .border-t {
        background-color: white !important;
        border-top: 1px solid #e5e7eb !important;
        padding-bottom: env(safe-area-inset-bottom, 16px) !important;
        min-height: 80px !important;
    }

    /* Force input visibility */
    .flex.gap-2 {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* Ensure messages area doesn't overlap input */
    .flex-1.overflow-y-auto {
        padding-bottom: 100px !important;
        margin-bottom: -100px !important;
    }
}
</style>