<template>
    <div class="tw-p-4">
        <h2 class="tw-text-2xl tw-font-semibold tw-text-gray-800 tw-mb-8 tw-text-center">
            {{ isEditMode ? 'Chỉnh sửa bài viết' : 'Thêm bài viết mới' }}
        </h2>

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

            <!-- Image Upload -->
            <div class="tw-flex tw-flex-col">
                <label class="tw-font-medium tw-text-gray-700 tw-mb-2">Hình ảnh</label>
                <div class="tw-flex tw-items-center tw-gap-4">
                    <div v-if="formData.image" class="tw-relative">
                        <img :src="formData.image" class="tw-w-32 tw-h-32 tw-object-cover tw-rounded" />
                        <button type="button" @click="removeImage"
                            class="tw-absolute tw-top-0 tw-right-0 tw-bg-red-500 tw-text-white tw-rounded-full tw-w-6 tw-h-6 tw-flex tw-items-center tw-justify-center hover:tw-bg-red-600">
                            ×
                        </button>
                    </div>
                    <div>
                        <input type="file" id="blog-image" accept="image/*" @change="handleImageUpload"
                            class="tw-hidden" />
                        <label for="blog-image"
                            class="tw-cursor-pointer tw-px-4 tw-py-2 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm hover:tw-bg-gray-50">
                            {{ formData.image ? 'Thay đổi hình ảnh' : 'Tải lên hình ảnh' }}
                        </label>
                    </div>
                </div>
                <span v-if="errors.image" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.image }}</span>
            </div>

            <!-- Status -->
            <div class="tw-flex tw-flex-col">
                <label class="tw-font-medium tw-text-gray-700 tw-mb-2">Trạng thái *</label>
                <select v-model="formData.status"
                    class="tw-w-full tw-px-3 tw-py-3 tw-border tw-border-gray-300 tw-rounded-md tw-text-sm focus:tw-outline-none focus:tw-border-green-500 focus:tw-ring-2 focus:tw-ring-green-100">
                    <option value="draft">Bản nháp</option>
                    <option value="published">Đã xuất bản</option>
                    <option value="archived">Lưu trữ</option>
                </select>
                <span v-if="errors.status" class="tw-text-red-500 tw-text-sm tw-mt-1">{{ errors.status }}</span>
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

            <div class="tw-flex tw-justify-end tw-gap-4 tw-pt-4 tw-border-t tw-border-gray-200">
                <button type="button" @click="handleCancel"
                    class="tw-px-4 tw-py-2 tw-border tw-rounded tw-text-gray-600 hover:tw-bg-gray-50">
                    Hủy
                </button>
                <button type="submit" :disabled="loading"
                    class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark disabled:tw-opacity-50 disabled:tw-cursor-not-allowed">
                    {{ loading ? 'Đang xử lý...' : isEditMode ? 'Cập nhật' : 'Thêm mới' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useBlog } from '@/composables/useBlog'

const QuillEditor = defineAsyncComponent(() => {
    return import('@vueup/vue-quill').then(module => {
        if (process.client) {
            import('@vueup/vue-quill/dist/vue-quill.snow.css')
        }
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

const dataLoaded = ref(false)

watch(
    () => route.params.id,
    () => {
        dataLoaded.value = false
    }
)

onMounted(async () => {
    if (isEditMode.value) {
        await fetchBlog(route.params.id)
    }
})

watch(
    () => blog.value,
    (val) => {
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
    },
    { immediate: true }
)

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

    const textLength = getTextLength(formData.value.content)
    if (!formData.value.content || textLength < 50) {
        errors.value.content = 'Nội dung phải có ít nhất 50 ký tự'
        isValid = false
    }

    return isValid
}

const handleSubmit = async () => {
    if (!validateForm()) return

    try {

        if (isEditMode.value) {
            const jsonData = {
                title: formData.value.title,
                description: formData.value.description,
                content: formData.value.content,
                status: formData.value.status
            };

            await updateBlogJson(route.params.id, jsonData)
        } else {
            const data = new FormData()
            data.append('title', formData.value.title)
            data.append('description', formData.value.description)
            data.append('content', formData.value.content)
            data.append('status', formData.value.status)
            if (formData.value.imageFile instanceof File) {
                data.append('image', formData.value.imageFile)
            }
            await createBlog(data)
        }
        router.push('/admin/blogs')
    } catch (err) {
        if (err.errors) {
            errors.value = err.errors
        } else {
            console.error('Error:', err)
        }
    }
}

const handleCancel = () => {
    router.push('/admin/blogs')
}

</script>

<style scoped>
.tw-bg-primary {
    background-color: #3bb77e;
}

.tw-bg-primary-dark {
    background-color: #2ea16d;
}
</style>