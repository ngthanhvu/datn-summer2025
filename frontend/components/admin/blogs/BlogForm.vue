<template>
    <h2 class="tw-text-2xl tw-font-semibold tw-text-gray-800 tw-mb-8 tw-text-center tw-mt-5">
        {{ isEditMode ? 'Chỉnh sửa bài viết' : 'Thêm bài viết mới' }}
    </h2>
    <div
        class="tw-p-6 tw-pb-28 tw-w-[90%] tw-bg-white mx-auto tw-border tw-border-gray-200 tw-mb-[50px] tw-rounded-md tw-relative">
        <form @submit.prevent="handleSubmit" class="tw-flex tw-flex-col lg:tw-flex-row tw-gap-8 tw-mb-3">
            <!-- Cột trái -->
            <div class="tw-flex-1 tw-space-y-4">
                <!-- Title -->
                <div class="tw-flex tw-flex-col">
                    <label for="blog-title" class="tw-font-medium tw-text-gray-700 tw-mb-2">Tiêu đề <span
                            class="tw-text-red-500">*</span></label>
                    <input id="blog-title" v-model="formData.title" type="text"
                        class="tw-w-full tw-border tw-rounded tw-px-3 tw-py-2 focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                        :class="{ 'tw-border-red-500': errors.title }" placeholder="Nhập tiêu đề bài viết..." />
                    <span v-if="errors.title" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.title }}</span>
                </div>
                <!-- Description -->
                <div class="tw-flex tw-flex-col">
                    <label for="blog-description" class="tw-font-medium tw-text-gray-700 tw-mb-2">Mô tả <span
                            class="tw-text-red-500">*</span></label>
                    <textarea id="blog-description" v-model="formData.description"
                        class="tw-w-full tw-px-3 tw-py-3 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm tw-transition-colors tw-resize-y tw-font-inherit focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100"
                        :class="{ 'tw-border-red-500': errors.description }" placeholder="Nhập mô tả bài viết..."
                        rows="3"></textarea>
                    <span v-if="errors.description" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.description
                        }}</span>
                </div>
                <!-- Image Upload -->
                <div class="tw-flex tw-flex-col">
                    <label class="tw-font-medium tw-text-gray-700 tw-mb-2">Hình ảnh <span
                            class="tw-text-red-500">*</span></label>
                    <div>
                        <label
                            class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-w-full tw-h-40 tw-border-2 tw-border-gray-300 tw-border-dashed tw-rounded-lg tw-cursor-pointer hover:tw-bg-gray-50">
                            <div class="tw-flex tw-flex-col tw-items-center tw-justify-center">
                                <i class="fas fa-cloud-upload-alt tw-text-3xl tw-text-gray-400 tw-mb-2"></i>
                                <span class="tw-text-gray-500 tw-font-semibold">Click để tải ảnh lên</span>
                                <span class="tw-text-xs tw-text-gray-400">PNG, JPG, GIF (tối đa 2MB)</span>
                            </div>
                            <input type="file" id="blog-image" accept="image/*" class="tw-hidden"
                                @change="handleImageUpload" />
                        </label>
                    </div>
                    <div v-if="errors.image" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.image }}</div>
                    <div v-if="formData.image" class="tw-relative tw-w-48 tw-h-48 tw-mt-4">
                        <img :src="formData.image"
                            class="tw-w-full tw-h-full tw-object-cover tw-rounded-lg tw-shadow" />
                        <button type="button" @click="removeImage"
                            class="tw-absolute tw-top-2 tw-right-2 tw-p-2 tw-rounded-full tw-bg-white tw-shadow hover:tw-bg-gray-100"
                            title="Xóa ảnh">
                            <i class="fas fa-times tw-text-red-500"></i>
                        </button>
                    </div>
                </div>
                <!-- Status -->
                <div class="tw-flex tw-flex-col">
                    <label class="tw-font-medium tw-text-gray-700 tw-mb-2">Trạng thái <span
                            class="tw-text-red-500">*</span></label>
                    <div class="tw-flex tw-items-center tw-gap-2">
                        <button type="button"
                            @click="formData.status = formData.status === 'published' ? 'draft' : 'published'" :class="[
                                'tw-relative tw-inline-flex tw-h-6 tw-w-11 tw-items-center tw-rounded-full tw-transition-colors tw-focus:outline-none tw-focus:ring-2 tw-focus:ring-green-400 tw-focus:ring-offset-2',
                                formData.status === 'published' ? 'tw-bg-[#3BB77E]' : 'tw-bg-gray-200'
                            ]">
                            <span :class="[
                                'tw-inline-block tw-h-4 tw-w-4 tw-transform tw-rounded-full tw-bg-white tw-transition-transform',
                                formData.status === 'published' ? 'tw-translate-x-6' : 'tw-translate-x-1'
                            ]"></span>
                        </button>
                        <span class="tw-ml-2">{{ formData.status === 'published' ? 'Đã xuất bản' : 'Nháp' }}</span>
                    </div>
                    <span v-if="errors.status" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.status }}</span>
                </div>
            </div>
            <!-- Cột phải: CKEditor -->
            <div class="tw-w-full lg:tw-w-1/2">
                <label class="tw-font-medium tw-text-gray-700 tw-mb-2">Nội dung <span
                        class="tw-text-red-500">*</span></label>
                <ClientOnly>
                    <CKEditor v-model="formData.content" :key="route.params.id || 'new'" />
                    <template #fallback>
                        <div class="tw-p-4 tw-text-center tw-text-gray-500">Đang tải editor...</div>
                    </template>
                </ClientOnly>
                <div class="tw-text-right tw-text-xs tw-text-gray-500 tw-mt-1">{{ getTextLength(formData.content) }} ký
                    tự</div>
                <span v-if="errors.content" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.content }}</span>
            </div>
        </form>
        <div class="tw-absolute tw-bottom-0 tw-right-0 tw-m-6 tw-flex tw-gap-4">
            <button type="button" @click="handleCancel"
                class="tw-px-4 tw-py-2 tw-border tw-rounded tw-text-gray-600 hover:tw-bg-gray-50">
                Hủy
            </button>
            <button type="submit" :disabled="loading"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark disabled:tw-opacity-50 disabled:tw-cursor-not-allowed">
                {{ loading ? 'Đang xử lý...' : isEditMode ? 'Cập nhật' : 'Thêm mới' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useBlog } from '@/composables/useBlog'

const QuillEditor = defineAsyncComponent(() => {
    return import('@vueup/vue-quill').then(module => {
        if (process.client) import('@vueup/vue-quill/dist/vue-quill.snow.css')
        return module.QuillEditor
    })
})

const route = useRoute()
const router = useRouter()
const { blog, loading, error, fetchBlog, createBlog, updateBlog, updateBlogJson } = useBlog()

const isEditMode = computed(() => route.params.id)
const formData = ref({
    title: '',
    description: '',
    content: '',
    status: 'draft',
    image: null,
    imageFile: null
})
const errors = ref({})
const dataLoaded = ref(false)

watch(() => route.params.id, () => { dataLoaded.value = false })

onMounted(async () => {
    if (isEditMode.value) await fetchBlog(route.params.id)
})

watch(() => blog.value, (val) => {
    if (isEditMode.value && val && !dataLoaded.value) {
        formData.value = {
            title: val.title || '',
            description: val.description || '',
            content: val.content || '',
            status: val.status || 'draft',
            image: val.image || null,
            imageFile: null
        }
        dataLoaded.value = true
    }
}, { immediate: true })

const getTextLength = (htmlContent) => {
    if (!htmlContent) return 0
    if (process.client) {
        const tempDiv = document.createElement('div')
        tempDiv.innerHTML = htmlContent
        return tempDiv.textContent?.length || 0
    }
    return 0
}

const handleImageUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        if (!file.type.match('image.*')) {
            errors.value.image = 'Vui lòng chọn file hình ảnh'
            return
        }
        formData.value.imageFile = file
        const reader = new FileReader()
        reader.onload = (e) => {
            formData.value.image = e.target.result
            errors.value.image = null
        }
        reader.readAsDataURL(file)
    }
}

const removeImage = () => {
    formData.value.image = null
    formData.value.imageFile = null
    const fileInput = document.getElementById('blog-image')
    if (fileInput) fileInput.value = ''
}

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
    if (!formData.value.content || getTextLength(formData.value.content) < 50) {
        errors.value.content = 'Nội dung phải có ít nhất 50 ký tự'
        isValid = false
    }
    return isValid
}

const buildFormData = () => {
    const data = new FormData()
    data.append('title', formData.value.title)
    data.append('description', formData.value.description)
    data.append('content', formData.value.content)
    data.append('status', formData.value.status)
    if (formData.value.imageFile instanceof File) {
        data.append('image', formData.value.imageFile)
    }
    return data
}

const handleSubmit = async () => {
    if (!validateForm()) return
    try {
        if (isEditMode.value) {
            if (formData.value.imageFile instanceof File) {
                await updateBlog(route.params.id, buildFormData())
            } else {
                await updateBlogJson(route.params.id, {
                    title: formData.value.title,
                    description: formData.value.description,
                    content: formData.value.content,
                    status: formData.value.status
                })
            }
        } else {
            await createBlog(buildFormData())
            alert('Thêm bài viết thành công!')
        }
        router.push('/admin/blogs')
    } catch (err) {
        if (err.errors) errors.value = err.errors
        else console.error('Error:', err)
    }
}

const handleCancel = () => router.push('/admin/blogs')
</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}

:deep(.ck-editor__editable_inline) {
    min-height: 400px;
    max-height: 600px;
}
</style>