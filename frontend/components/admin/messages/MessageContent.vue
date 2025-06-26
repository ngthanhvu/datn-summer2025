<template>
    <div class="messages-content tw-flex-1 tw-bg-gray-50 tw-flex tw-flex-col tw-h-full">
        <div v-if="message" class="tw-h-full tw-flex tw-flex-col">
            <!-- Message Header (sticky) -->
            <div class="tw-bg-white tw-px-6 tw-py-4 tw-border-b tw-sticky tw-top-0 tw-z-10">
                <div class="tw-flex tw-items-center tw-gap-4">
                    <img
                        :src="message.avatar ? message.avatar : 'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg'"
                        :alt="message.name"
                        class="tw-w-12 tw-h-12 tw-rounded-full tw-object-cover"
                    >
                    <div>
                        <h3 class="tw-font-semibold">{{ message.name }}</h3>
                        <p class="tw-text-sm tw-text-gray-500">{{ message.email }}</p>
                    </div>
                </div>
            </div>

            <!-- Messages (scrollable) -->
            <div class="tw-flex-1 tw-overflow-y-auto tw-p-6" ref="messagesContainer">
                <div class="tw-max-w-3xl tw-mx-auto tw-space-y-4">
                    <div v-for="(msg, index) in message.messages" :key="index" class="tw-flex"
                        :class="[msg.isAdmin ? 'tw-justify-end' : 'tw-justify-start']">
                        <div class="tw-flex tw-items-end tw-gap-2" :class="[msg.isAdmin ? 'tw-flex-row-reverse' : '']">
                            <img :src="msg.isAdmin ? adminAvatar : (message.avatar ? message.avatar : 'https://img.freepik.com/premium-vector/user-icons-includes-user-icons-people-icons-symbols-premiumquality-graphic-design-elements_981536-526.jpg')"
                                :alt="msg.isAdmin ? 'Admin' : message.name"
                                class="tw-w-8 tw-h-8 tw-rounded-full tw-object-cover">
                            <div :class="[
                                'tw-max-w-md tw-rounded-2xl tw-px-4 tw-py-2',
                                msg.isAdmin ? 'tw-bg-primary tw-text-white tw-rounded-br-none' : 'tw-bg-white tw-rounded-bl-none'
                            ]">
                                <div v-if="msg.attachment" class="tw-mb-2">
                                    <img
                                        v-if="/\.(jpg|jpeg|png|gif)$/i.test(msg.attachment)"
                                        :src="getAttachmentUrl(msg.attachment)"
                                        class="tw-max-w-xs tw-rounded tw-cursor-pointer"
                                        @click="openImage(getAttachmentUrl(msg.attachment))"
                                    >
                                    <a
                                        v-else
                                        :href="getAttachmentUrl(msg.attachment)"
                                        target="_blank"
                                        class="tw-flex tw-items-center tw-gap-2 tw-p-2 tw-bg-white tw-bg-opacity-20 tw-rounded"
                                    >
                                        <i class="fas fa-file"></i>
                                        <span class="tw-text-sm">{{ getFileName(msg.attachment) }}</span>
                                    </a>
                                </div>
                                <p v-if="msg.content">{{ msg.content }}</p>
                                <span class="tw-text-xs tw-opacity-75 tw-block tw-mt-1">{{ msg.time }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Input (sticky) -->
            <div class="tw-bg-white tw-px-6 tw-py-4 tw-border-t tw-sticky tw-bottom-0 tw-z-10">
                <div class="tw-max-w-3xl tw-mx-auto">
                    <div class="tw-flex tw-gap-4">
                        <input type="text" v-model="newMessage" @keyup.enter="handleSend" placeholder="Nhập tin nhắn..."
                            class="tw-flex-1 tw-border tw-rounded-full tw-px-6 tw-py-3 focus:tw-ring-2 focus:tw-ring-primary/20 focus:tw-border-primary">
                        <label class="tw-bg-gray-100 tw-rounded-full tw-w-12 tw-h-12 tw-flex tw-items-center tw-justify-center tw-cursor-pointer hover:tw-bg-gray-200">
                            <i class="fas fa-paperclip tw-text-lg"></i>
                            <input type="file" ref="fileInput" @change="handleFileSelect" class="tw-hidden" accept="image/*,.pdf,.doc,.docx">
                        </label>
                        <button @click="handleSend"
                            :disabled="(!newMessage.trim() && !selectedFile)"
                            class="tw-bg-primary tw-text-white tw-rounded-full tw-w-12 tw-h-12 tw-flex tw-items-center tw-justify-center hover:tw-bg-primary-dark disabled:tw-opacity-50 disabled:tw-cursor-not-allowed">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="selectedFile" class="tw-mt-2 tw-flex tw-items-center tw-gap-2 tw-p-2 tw-bg-gray-100 tw-rounded">
                <i class="fas fa-file tw-text-gray-600"></i>
                <span class="tw-text-sm tw-flex-1">{{ selectedFile.name }}</span>
                <button @click="removeFile" class="tw-text-red-500 hover:tw-text-red-700"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <div v-else class="tw-h-full tw-flex tw-items-center tw-justify-center tw-text-gray-500">
            <div class="tw-text-center">
                <i class="fas fa-comments tw-text-4xl tw-mb-2"></i>
                <p>Chọn một cuộc trò chuyện để bắt đầu</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick, defineExpose } from 'vue'
const runtimeConfig = typeof useRuntimeConfig === 'function' ? useRuntimeConfig() : null

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
    if (file) {
        selectedFile.value = file
    }
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

watch(() => props.message?.messages, async () => {
  await nextTick()
  scrollToBottom()
}, { deep: true })

onMounted(() => {
  scrollToBottom()
})

const getAttachmentUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http')) return path
  if (runtimeConfig && runtimeConfig.public && runtimeConfig.public.apiBaseUrl) {
    return runtimeConfig.public.apiBaseUrl + '/storage/' + path
  }
  return '/storage/' + path
}

const getFileName = (path) => path.split('/').pop()

const openImage = (src) => window.open(src, '_blank')

defineExpose({ scrollToBottom })
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
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