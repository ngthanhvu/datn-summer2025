<template>
    <div class="tw-space-y-2">
        <label :for="id" class="tw-block tw-text-sm tw-font-medium tw-text-gray-700">{{ label }}</label>

        <!-- Preview Image -->
        <div v-if="modelValue || previewUrl" class="tw-relative tw-w-32 tw-h-32 tw-mb-4">
            <img :src="typeof modelValue === 'string' ? modelValue : previewUrl"
                class="tw-w-full tw-h-full tw-object-cover tw-rounded-lg" />
            <button @click="removeImage"
                class="tw-absolute tw-w-[25%] tw-top-0 tw-right-0 tw-p-1 tw-bg-red-500 tw-rounded-full tw-text-white hover:tw-bg-red-600 tw-transform tw-translate-x-1/2 tw--translate-y-1/2">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Upload Button -->
        <div class="tw-flex tw-items-center tw-justify-center tw-w-full">
            <label :for="id"
                class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-w-full tw-h-32 tw-border-2 tw-border-gray-300 tw-border-dashed tw-rounded-lg tw-cursor-pointer hover:tw-bg-gray-50"
                :class="{ 'tw-border-red-500': error }">
                <div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-pt-5 tw-pb-6">
                    <i class="fas fa-cloud-upload-alt tw-text-2xl tw-text-gray-400 tw-mb-2"></i>
                    <p class="tw-text-sm tw-text-gray-500">
                        <span class="tw-font-semibold">Click để tải ảnh lên</span>
                    </p>
                    <p class="tw-text-xs tw-text-gray-500">PNG, JPG, GIF (tối đa 2MB)</p>
                </div>
                <input :id="id" type="file" class="tw-hidden" accept="image/*" @change="handleImageChange"
                    :required="required">
            </label>
        </div>

        <!-- Error Message -->
        <p v-if="error" class="tw-text-red-500 tw-text-sm">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    modelValue: {
        type: [File, String, null],
        default: null
    },
    label: {
        type: String,
        required: true
    },
    required: {
        type: Boolean,
        default: false
    },
    id: {
        type: String,
        default: () => `image-upload-${Math.random().toString(36).substr(2, 9)}`
    }
})

const emit = defineEmits(['update:modelValue', 'error'])

const previewUrl = ref(null)
const error = ref('')

const validateImage = (file) => {
    error.value = ''

    if (!file) {
        if (props.required) {
            error.value = 'Vui lòng chọn hình ảnh'
        }
        return false
    }

    // Check file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif']
    if (!allowedTypes.includes(file.type)) {
        error.value = 'Chỉ chấp nhận file ảnh (PNG, JPG, GIF)'
        return false
    }

    // Check file size (2MB)
    const maxSize = 2 * 1024 * 1024 // 2MB in bytes
    if (file.size > maxSize) {
        error.value = 'Kích thước file không được vượt quá 2MB'
        return false
    }

    return true
}

const handleImageChange = (event) => {
    const file = event.target.files[0]
    if (file) {
        if (validateImage(file)) {
            const reader = new FileReader()
            reader.onload = (e) => {
                previewUrl.value = e.target.result
                emit('update:modelValue', file)
            }
            reader.readAsDataURL(file)
        } else {
            event.target.value = '' // Reset input
            emit('update:modelValue', null)
        }
    }
}

const removeImage = () => {
    previewUrl.value = null
    emit('update:modelValue', null)
    if (props.required) {
        error.value = 'Vui lòng chọn hình ảnh'
    }
}

// Watch for required prop changes
watch(() => props.required, (newVal) => {
    if (newVal && !props.modelValue) {
        error.value = 'Vui lòng chọn hình ảnh'
    }
})

// Initial validation
if (props.required && !props.modelValue) {
    error.value = 'Vui lòng chọn hình ảnh'
}
</script>