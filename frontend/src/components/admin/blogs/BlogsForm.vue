<template>
    <h2 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6 sm:mb-8 text-center mt-5">
        {{ isEditMode ? 'Chỉnh sửa bài viết' : 'Thêm bài viết mới' }}
    </h2>
    <div
        class="p-4 sm:p-6 pb-20 sm:pb-28 w-full sm:w-[90%] bg-white mx-auto border border-gray-200 mb-[50px] rounded-md relative">
        <form @submit.prevent="handleSubmit" class="flex flex-col lg:flex-row gap-6 sm:gap-8 mb-3">
            <!-- Cột trái -->
            <div class="flex-1 space-y-4">
                <!-- Title -->
                <div class="flex flex-col">
                    <label for="blog-title" class="font-medium text-gray-700 mb-2">Tiêu đề <span
                            class="text-red-500">*</span></label>
                    <input id="blog-title" v-model="formData.title" type="text"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                        :class="{ 'border-red-500': errors.title }" placeholder="Nhập tiêu đề bài viết..." />
                    <span v-if="errors.title" class="text-red-500 text-sm mt-1">{{ errors.title }}</span>
                </div>

                <!-- Category -->
                <div class="flex flex-col">
                    <label for="blog-category" class="font-medium text-gray-700 mb-2">Danh mục</label>
                    <select id="blog-category" v-model="formData.blog_category_id"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100">
                        <option value="">Chọn danh mục</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                    <span v-if="errors.blog_category_id" class="text-red-500 text-sm mt-1">{{ errors.blog_category_id
                    }}</span>
                </div>

                <!-- Description -->
                <div class="flex flex-col">
                    <label for="blog-description" class="font-medium text-gray-700 mb-2">Mô tả <span
                            class="text-red-500">*</span></label>
                    <textarea id="blog-description" v-model="formData.description"
                        class="w-full px-3 py-3 border border-gray-300 rounded-md text-sm transition-colors resize-y font-inherit focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                        :class="{ 'border-red-500': errors.description }" placeholder="Nhập mô tả bài viết..."
                        rows="3"></textarea>
                    <span v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description
                        }}</span>
                </div>
                <!-- Image Upload -->
                <div class="flex flex-col">
                    <label class="font-medium text-gray-700 mb-2">Hình ảnh <span class="text-red-500">*</span></label>
                    <div>
                        <label
                            class="flex flex-col items-center justify-center w-full h-32 sm:h-40 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer hover:bg-gray-50">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-cloud-upload-alt text-2xl sm:text-3xl text-gray-400 mb-2"></i>
                                <span class="text-gray-500 font-semibold text-sm sm:text-base">Click để tải ảnh
                                    lên</span>
                                <span class="text-xs text-gray-400">PNG, JPG, GIF (tối đa 2MB)</span>
                            </div>
                            <input type="file" id="blog-image" accept="image/*" class="hidden"
                                @change="handleImageUpload" />
                        </label>
                    </div>
                    <div v-if="errors.image" class="text-red-500 text-sm mt-1">{{ errors.image }}</div>
                    <div v-if="formData.image" class="relative w-32 sm:w-48 h-32 sm:h-48 mt-4">
                        <img :src="formData.image" class="w-full h-full object-cover rounded-lg shadow" />
                        <button type="button" @click="removeImage"
                            class="absolute top-2 right-2 p-1 sm:p-2 rounded-full bg-white shadow hover:bg-gray-100"
                            title="Xóa ảnh">
                            <i class="fas fa-times text-red-500 text-sm sm:text-base"></i>
                        </button>
                    </div>
                </div>
                <!-- Status -->
                <div class="flex flex-col">
                    <label class="font-medium text-gray-700 mb-2">Trạng thái <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <button type="button"
                            @click="formData.status = formData.status === 'published' ? 'draft' : 'published'" :class="[
                                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2',
                                formData.status === 'published' ? 'bg-[#3BB77E]' : 'bg-gray-200'
                            ]">
                            <span :class="[
                                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                formData.status === 'published' ? 'translate-x-6' : 'translate-x-1'
                            ]"></span>
                        </button>
                        <span class="ml-2 text-sm">{{ formData.status === 'published' ? 'Đã xuất bản' : 'Nháp' }}</span>
                    </div>
                    <span v-if="errors.status" class="text-red-500 text-sm mt-1">{{ errors.status }}</span>
                </div>
            </div>
            <!-- Cột phải: CKEditor -->
            <div class="w-full lg:w-1/2">
                <label class="font-medium text-gray-700 mb-2">Nội dung <span class="text-red-500">*</span></label>
                <CKEditor v-model="formData.content" :key="route.params.id || 'new'" />
                <div class="text-right text-xs text-gray-500 mt-1">{{ getTextLength(formData.content) }} ký
                    tự</div>
                <span v-if="errors.content" class="text-red-500 text-sm mt-1">{{ errors.content }}</span>
            </div>
        </form>
        <div class="absolute bottom-0 right-0 m-4 sm:m-6 flex flex-col sm:flex-row gap-2 sm:gap-4">
            <button type="button" @click="handleCancel"
                class="w-full sm:w-auto px-4 py-2 border rounded text-gray-600 hover:bg-gray-50 text-center">
                Hủy
            </button>
            <button type="button" @click="handleSubmit" :disabled="loading"
                class="w-full sm:w-auto bg-primary text-white rounded px-4 py-2 cursor-pointer hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed">
                {{ loading ? 'Đang xử lý...' : isEditMode ? 'Cập nhật' : 'Thêm mới' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useBlog } from '../../../composable/useBlogs'
import CKEditor from '../../CKEditor.vue'
import { push } from 'notivue'

// Define props
const props = defineProps({
    isEdit: {
        type: Boolean,
        default: false
    }
})

const route = useRoute()
const router = useRouter()
const { blog, loading, categories, fetchBlog, createBlog, updateBlog, updateBlogJson, fetchCategories } = useBlog()

const isEditMode = computed(() => props.isEdit || route.params.id)
const formData = ref({
    title: '',
    description: '',
    content: '',
    status: 'draft',
    blog_category_id: '',
    image: null,
    imageFile: null
})
const errors = ref({})
const dataLoaded = ref(false)

watch(() => route.params.id, () => { dataLoaded.value = false })

onMounted(async () => {
    await fetchCategories()
    if (isEditMode.value) await fetchBlog(route.params.id)
})

watch(() => blog.value, (val) => {
    if (isEditMode.value && val && !dataLoaded.value) {
        formData.value = {
            title: val.title || '',
            description: val.description || '',
            content: val.content || '',
            status: val.status || 'draft',
            blog_category_id: val.blog_category_id || '',
            image: val.image || null,
            imageFile: null
        }
        dataLoaded.value = true
    }
}, { immediate: true })

const getTextLength = (htmlContent) => {
    if (!htmlContent) return 0
    try {
        const tempDiv = document.createElement('div')
        tempDiv.innerHTML = htmlContent
        return tempDiv.textContent?.length || 0
    } catch (e) {
        return 0
    }
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
    if (!formData.value.blog_category_id) {
        errors.value.blog_category_id = 'Vui lòng chọn danh mục'
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
    if (formData.value.blog_category_id) {
        data.append('blog_category_id', formData.value.blog_category_id)
    }
    if (formData.value.imageFile instanceof File) {
        data.append('image', formData.value.imageFile)
    }
    return data
}

const handleSubmit = async () => {

    if (!validateForm()) {
        return
    }

    try {
        if (isEditMode.value) {
            if (formData.value.imageFile instanceof File) {
                await updateBlog(route.params.id, buildFormData())
            } else {
                await updateBlogJson(route.params.id, {
                    title: formData.value.title,
                    description: formData.value.description,
                    content: formData.value.content,
                    status: formData.value.status,
                    blog_category_id: formData.value.blog_category_id || null
                })
            }
            push.success('Cập nhật bài viết thành công!')
        } else {
            await createBlog(buildFormData())
            push.success('Thêm bài viết thành công!')
        }
        router.push('/admin/blogs')
    } catch (err) {
        console.error('Submit error:', err)
        if (err.errors) errors.value = err.errors
        else console.error('Error:', err)
    }
}

const handleCancel = () => router.push('/admin/blogs')
</script>

<style scoped>
.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
}

:deep(.ck-editor__editable_inline) {
    min-height: 300px;
    max-height: 500px;
}

@media (max-width: 640px) {
    :deep(.ck-editor__editable_inline) {
        min-height: 250px;
        max-height: 400px;
    }
}
</style>