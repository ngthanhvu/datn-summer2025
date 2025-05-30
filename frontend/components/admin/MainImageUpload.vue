<template>
    <div class="tw-space-y-4">
        <!-- Image Upload Button -->
        <div class="tw-flex tw-items-center tw-gap-4">
            <label class="tw-cursor-pointer tw-bg-gray-100 tw-px-4 tw-py-2 tw-rounded hover:tw-bg-gray-200">
                <input type="file" accept="image/*" class="tw-hidden" @change="handleImageUpload">
                <i class="fas fa-upload tw-mr-2"></i>
                Chọn ảnh chính
            </label>
            <span v-if="modelValue" class="tw-text-green-600">
                <i class="fas fa-check tw-mr-1"></i>
                Đã chọn ảnh
            </span>
        </div>

        <!-- Image Preview -->
        <div v-if="previewUrl" class="tw-relative tw-w-48 tw-h-48">
            <img :src="previewUrl" class="tw-w-full tw-h-full tw-object-cover tw-rounded-lg tw-shadow">
            <!-- Remove Button -->
            <button @click="removeImage"
                class="tw-absolute tw-top-2 tw-right-2 tw-p-2 tw-rounded-full tw-bg-white tw-shadow hover:tw-bg-gray-100"
                title="Xóa ảnh">
                <i class="fas fa-times tw-text-red-500"></i>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    modelValue: {
        type: [File, null],
        default: null
    }
})

const emit = defineEmits(['update:modelValue'])

const previewUrl = ref(null)

// Watch for changes to update preview
watch(() => props.modelValue, (newFile) => {
    if (newFile) {
        previewUrl.value = URL.createObjectURL(newFile)
    } else {
        previewUrl.value = null
    }
}, { immediate: true })

// Handle image upload
const handleImageUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        emit('update:modelValue', file)
    }
}

// Remove image
const removeImage = () => {
    emit('update:modelValue', null)
}
</script>