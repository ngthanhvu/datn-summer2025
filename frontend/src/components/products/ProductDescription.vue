<template>
    <div class="prose max-w-none">
        <h3 class="text-xl font-bold mb-4">Mô tả sản phẩm</h3>
        <div class="relative">
            <!-- Văn bản rút gọn -->
            <div v-if="!isExpanded && truncatedDescription" 
                 v-html="truncatedDescription" 
                 class="overflow-hidden">
            </div>
            
            <!-- Văn bản đầy đủ -->
            <div v-if="isExpanded || !truncatedDescription" 
                 v-html="description" 
                 class="overflow-visible">
            </div>
            
            <!-- Nút "Xem thêm" -->
            <div v-if="showReadMoreButton" class="mt-4 text-center">
                <button 
                    @click="toggleDescription" 
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-[#81AACC] bg-white border border-[#81AACC] rounded-md hover:bg-[#81AACC] hover:text-white transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#81AACC]"
                >
                    <span v-if="!isExpanded">
                        Xem thêm
                    </span>
                    <span v-else>
                        Thu gọn
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'

const props = defineProps({
    description: {
        type: String,
        required: true
    }
})

const isExpanded = ref(false)
const showReadMoreButton = ref(false)
const truncatedDescription = ref('')

// Hàm để tạo văn bản rút gọn
const createTruncatedDescription = (text, maxLength = 800) => {
    if (!text || text.length <= maxLength) {
        return text
    }
    
    let truncateIndex = maxLength
    for (let i = maxLength; i >= maxLength - 100; i--) {
        if (text[i] === '.' || text[i] === '!' || text[i] === '?' || text[i] === '\n') {
            truncateIndex = i + 1
            break
        }
    }
    
    return text.substring(0, truncateIndex) + '...'
}

const checkIfTruncationNeeded = async () => {
    await nextTick()
    
    if (!props.description) {
        showReadMoreButton.value = false
        return
    }
    
    if (props.description.length <= 800) {
        showReadMoreButton.value = false
        truncatedDescription.value = ''
        return
    }
    
    truncatedDescription.value = createTruncatedDescription(props.description)
    showReadMoreButton.value = true
}

const toggleDescription = () => {
    isExpanded.value = !isExpanded.value
}

onMounted(() => {
    checkIfTruncationNeeded()
})

import { watch } from 'vue'
watch(() => props.description, () => {
    checkIfTruncationNeeded()
}, { immediate: true })
</script>

<style scoped>
.prose {
    line-height: 1.6;
}

.prose p {
    margin-bottom: 1rem;
}

.prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    font-weight: 600;
}

.prose ul, .prose ol {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.prose li {
    margin-bottom: 0.5rem;
}
</style>