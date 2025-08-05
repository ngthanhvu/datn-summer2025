<template>
    <div class="messages-content flex-1 bg-gray-50 flex flex-col h-full">
        <div v-if="message" class="h-full flex flex-col">
            <!-- Message Header (sticky) -->
            <div class="bg-white px-6 py-4 border-b border-gray-300 sticky top-0 z-10">
                <div class="flex items-center gap-4">
                    <img :src="getAvatarUrl(message.avatar)" :alt="message.name"
                        class="w-12 h-12 rounded-full object-cover">
                    <div>
                        <h3 class="font-semibold">{{ message.name }}</h3>
                        <p class="text-sm text-gray-500">{{ message.email }}</p>
                    </div>
                </div>
            </div>

            <!-- Messages (scrollable) -->
            <div class="flex-1 overflow-y-auto p-6" ref="messagesContainer">
                <div class="max-w-3xl mx-auto space-y-4">
                    <div v-for="(msg, index) in message.messages" :key="index" class="flex"
                        :class="[msg.isAdmin ? 'justify-end' : 'justify-start']">
                        <div class="flex items-end gap-2" :class="[msg.isAdmin ? 'flex-row-reverse' : '']">
                            <img :src="msg.isAdmin ? getAvatarUrl(adminAvatar) : getAvatarUrl(message.avatar)"
                                :alt="msg.isAdmin ? 'Admin' : message.name" class="w-8 h-8 rounded-full object-cover">
                            <div :class="[
                                'max-w-md rounded-2xl px-4 py-2',
                                msg.isAdmin ? 'bg-primary text-white rounded-br-none' : 'bg-white rounded-bl-none'
                            ]">
                                <div v-if="msg.attachment" class="mb-2">
                                    <img v-if="/\.(jpg|jpeg|png|gif)$/i.test(msg.attachment)"
                                        :src="getAvatarUrl(msg.attachment)" class="max-w-xs rounded cursor-pointer"
                                        @click="openImage(getAvatarUrl(msg.attachment))">
                                    <a v-else :href="getAvatarUrl(msg.attachment)" target="_blank"
                                        class="flex items-center gap-2 p-2 bg-white bg-opacity-20 rounded">
                                        <i class="fas fa-file"></i>
                                        <span class="text-sm">{{ getFileName(msg.attachment) }}</span>
                                    </a>
                                </div>
                                <p v-if="msg.content">{{ msg.content }}</p>
                                <span class="text-xs opacity-75 block mt-1">{{ msg.time }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Input (sticky) -->
            <div class="bg-white px-6 py-4 border-t border-gray-300 sticky bottom-0 z-10">
                <div class="max-w-3xl mx-auto">
                    <div class="flex gap-4">
                        <input type="text" v-model="newMessage" @keyup.enter="handleSend" placeholder="Nhập tin nhắn..."
                            class="flex-1 border border-gray-300 rounded-full px-6 py-3 focus:ring-1 focus:ring-[#3BB77E] focus:border-[#3BB77E]">
                        <label
                            class="bg-gray-100 rounded-full w-12 h-12 flex items-center justify-center cursor-pointer hover:bg-gray-200">
                            <i class="fas fa-paperclip text-lg"></i>
                            <input type="file" ref="fileInput" @change="handleFileSelect" class="hidden"
                                accept="image/*,.pdf,.doc,.docx">
                        </label>
                        <button @click="handleSend" :disabled="(!newMessage.trim() && !selectedFile)"
                            class="bg-primary text-white rounded-full w-12 h-12 flex items-center justify-center hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="selectedFile" class="mt-2 flex items-center gap-2 p-2 bg-gray-100 rounded">
                <i class="fas fa-file text-gray-600"></i>
                <span class="text-sm flex-1">{{ selectedFile.name }}</span>
                <button @click="removeFile" class="text-red-500 hover:text-red-700"><i
                        class="fas fa-times"></i></button>
            </div>
        </div>
        <div v-else class="h-full flex items-center justify-center text-gray-500">
            <div class="text-center">
                <i class="fas fa-comments text-4xl mb-2"></i>
                <p>Chọn một cuộc trò chuyện để bắt đầu</p>
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
    if (!avatar) return defaultAvatar
    if (avatar.startsWith('http')) return avatar
    let url = `${apiBaseUrl}/${avatar.replace(/^\/+/, '')}`
    return url.replace(/\/{2,}storage\//, '/storage/')
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
.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
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
</style>