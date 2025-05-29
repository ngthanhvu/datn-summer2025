<template>
    <div class="tw-space-y-4">
        <!-- Image Upload Button -->
        <div class="tw-flex tw-items-center tw-gap-4">
            <label class="tw-cursor-pointer tw-bg-gray-100 tw-px-4 tw-py-2 tw-rounded hover:tw-bg-gray-200">
                <input type="file" multiple accept="image/*" class="tw-hidden" @change="handleImageUpload">
                <i class="fas fa-upload tw-mr-2"></i>
                Chọn ảnh phụ
            </label>
            <span v-if="modelValue.length > 0" class="tw-text-gray-600">
                Đã chọn {{ modelValue.length }} ảnh
            </span>
        </div>

        <!-- Images Preview Grid -->
        <div v-if="previewUrls.length > 0" class="tw-grid tw-grid-cols-4 tw-gap-4">
            <div v-for="(preview, index) in previewUrls" :key="index" class="tw-relative tw-group">
                <!-- Image Preview -->
                <img :src="preview" class="tw-w-full tw-h-32 tw-object-cover tw-rounded-lg tw-shadow">

                <!-- Remove Button -->
                <button @click="removeImage(index)"
                    class="tw-absolute tw-top-2 tw-right-2 tw-p-2 tw-rounded-full tw-bg-white tw-shadow tw-opacity-0 group-hover:tw-opacity-100 tw-transition-opacity"
                    title="Xóa ảnh">
                    <i class="fas fa-times tw-text-red-500"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['update:modelValue'])

const previewUrls = ref([])

// Watch for changes to update previews
watch(() => props.modelValue, (newFiles) => {
    previewUrls.value = newFiles.map(file => URL.createObjectURL(file))
}, { immediate: true, deep: true })

// Handle images upload
const handleImageUpload = (event) => {
    const newFiles = Array.from(event.target.files)
    emit('update:modelValue', [...props.modelValue, ...newFiles])
}

// Remove image
const removeImage = (index) => {
    const updatedFiles = [...props.modelValue]
    updatedFiles.splice(index, 1)
    emit('update:modelValue', updatedFiles)
}
</script>