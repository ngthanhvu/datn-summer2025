<template>
    <div class="tw-p-4">
        <h2 class="tw-text-2xl tw-font-semibold tw-text-gray-800 tw-mb-8 tw-text-center">Thêm bài viết mới</h2>

        <form @submit.prevent="handleSubmit" class="tw-flex tw-flex-col tw-gap-6 tw-mb-8">
            <!-- Title -->
            <div class="tw-flex tw-flex-col">
                <label for="blog-title" class="tw-font-medium tw-text-gray-700 tw-mb-2">Tiêu đề *</label>
                <input id="blog-title" v-model="formData.title" type="text"
                    class="tw-w-full tw-px-3 tw-py-3 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm tw-transition-colors focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                    :class="{ 'tw-border-red-500': errors.title }" placeholder="Nhập tiêu đề bài viết..." />
                <span v-if="errors.title" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.title }}</span>
            </div>

            <!-- Description -->
            <div class="tw-flex tw-flex-col">
                <label for="blog-description" class="tw-font-medium tw-text-gray-700 tw-mb-2">Mô tả *</label>
                <textarea id="blog-description" v-model="formData.description"
                    class="tw-w-full tw-px-3 tw-py-3 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm tw-transition-colors tw-resize-y tw-font-inherit focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                    :class="{ 'tw-border-red-500': errors.description }" placeholder="Nhập mô tả bài viết..."
                    rows="3"></textarea>
                <span v-if="errors.description" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.description
                }}</span>
            </div>

            <!-- File Upload -->
            <div class="tw-flex tw-flex-col">
                <label for="blog-file" class="tw-font-medium tw-text-gray-700 tw-mb-2">Tải lên file</label>
                <input id="blog-file" type="file"
                    class="tw-w-full tw-px-3 tw-py-3 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm tw-transition-colors focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                    @change="handleFileUpload" accept="image/*,.pdf,.doc,.docx" />
                <div v-if="formData.uploadedFile"
                    class="tw-flex tw-items-center tw-gap-2 tw-mt-2 tw-p-2 tw-bg-gray-100 tw-rounded">
                    <span class="tw-text-sm tw-text-gray-700">{{ formData.uploadedFile.name }}</span>
                    <button type="button" @click="removeFile"
                        class="tw-bg-red-500 tw-text-white tw-border-0 tw-rounded-full tw-w-5 tw-h-5 tw-cursor-pointer tw-text-xs tw-flex tw-items-center tw-justify-center hover:tw-bg-red-600">×</button>
                </div>
            </div>

            <!-- Status Toggle -->
            <div class="tw-flex tw-flex-col">
                <label class="tw-font-medium tw-text-gray-700 tw-mb-2">Trạng thái *</label>
                <div class="tw-flex tw-items-center tw-gap-3">
                    <label class="tw-relative tw-inline-block tw-w-12 tw-h-6">
                        <input type="checkbox" v-model="formData.status" class="tw-opacity-0 tw-w-0 tw-h-0" />
                        <span
                            class="tw-absolute tw-cursor-pointer tw-top-0 tw-left-0 tw-right-0 tw-bottom-0 tw-bg-gray-300 tw-transition-all tw-duration-300 tw-rounded-full before:tw-absolute before:tw-content-[''] before:tw-h-4 before:tw-w-4 before:tw-left-1 before:tw-bottom-1 before:tw-bg-white before:tw-transition-all before:tw-duration-300 before:tw-rounded-full"
                            :class="formData.status ? 'tw-bg-green-500 before:tw-translate-x-6' : ''"></span>
                    </label>
                    <span class="tw-text-sm tw-text-gray-700 tw-font-medium">
                        {{ formData.status ? 'Đã xuất bản' : 'Bản nháp' }}</span>
                </div>
            </div>

            <!-- Content with Vue Quill -->
            <div class="tw-flex tw-flex-col">
                <label class="tw-font-medium tw-text-gray-700 tw-mb-2">Nội dung *</label>
                <ClientOnly>
                    <QuillEditor v-model:content="formData.content" contentType="html" :options="quillOptions"
                        class="tw-rounded-md tw-min-h-80" :class="{ 'tw-border-red-500': errors.content }" />
                    <template #fallback>
                        <div class="tw-p-4 tw-text-center tw-text-gray-500">Đang tải editor...</div>
                    </template>
                </ClientOnly>
                <div class="tw-text-right tw-text-xs tw-text-gray-500 tw-mt-1">{{ getTextLength(formData.content) }} ký
                    tự</div>
                <span v-if="errors.content" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.content }}</span>
            </div>
        </form>

        <div class="tw-flex tw-justify-end tw-gap-4 tw-pt-4 tw-border-t tw-border-gray-200">
            <button type="button" @click="handleCancel"
                class="tw-px-4 tw-py-2 tw-border tw-rounded tw-text-gray-600 hover:tw-bg-gray-50">
                Hủy
            </button>
            <button type="button" @click="handleSubmit"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark">
                Thêm mới
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const QuillEditor = defineAsyncComponent(() => {
    return import('@vueup/vue-quill').then(module => {
        if (process.client) {
            import('@vueup/vue-quill/dist/vue-quill.snow.css')
        }
        return module.QuillEditor
    })
})

const emit = defineEmits(['submit', 'cancel'])

const formData = ref({
    title: '',
    description: '',
    content: '',
    status: false,
    uploadedFile: null
})

const errors = ref({})

const quillOptions = {
    theme: 'snow',
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ 'header': 1 }, { 'header': 2 }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'script': 'sub' }, { 'script': 'super' }],
            [{ 'indent': '-1' }, { 'indent': '+1' }],
            [{ 'direction': 'rtl' }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'font': [] }],
            [{ 'align': [] }],
            ['clean'],
            ['link', 'image']
        ]
    },
    placeholder: 'Nhập nội dung bài viết...'
}

// Get text length from HTML content
const getTextLength = (htmlContent) => {
    if (!htmlContent) return 0
    if (process.client) {
        const tempDiv = document.createElement('div')
        tempDiv.innerHTML = htmlContent
        return tempDiv.textContent?.length || 0
    }
    return 0
}

// Handle file upload
const handleFileUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        formData.value.uploadedFile = file
    }
}

// Remove uploaded file
const removeFile = () => {
    formData.value.uploadedFile = null
    if (process.client) {
        const fileInput = document.getElementById('blog-file')
        if (fileInput) fileInput.value = ''
    }
}

// Reset form to initial state
const resetForm = () => {
    formData.value = {
        title: '',
        description: '',
        content: '',
        status: false,
        uploadedFile: null
    }
    errors.value = {}
}

// Validate form
const validateForm = () => {
    errors.value = {}
    let isValid = true

    if (!formData.value.title || formData.value.title.trim().length < 3) {
        errors.value.title = 'Tiêu đề phải có ít nhất 3 ký tự'
        isValid = false
    }

    if (!formData.value.description || formData.value.description.trim().length < 10) {
        errors.value.description = 'Mô tả phải có ít nhất 10 ký tự'
        isValid = false
    }

    const textLength = getTextLength(formData.value.content)
    if (!formData.value.content || textLength < 50) {
        errors.value.content = 'Nội dung phải có ít nhất 50 ký tự'
        isValid = false
    }

    return isValid
}

// Handle form submission
const handleSubmit = () => {
    if (validateForm()) {
        emit('submit', formData.value)
        // Reset form after successful submission
        resetForm()
    }
}

// Handle cancel
const handleCancel = () => {
    resetForm()
    emit('cancel')
}

// Initialize form on mount
onMounted(() => {
    resetForm()
})
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}
</style>