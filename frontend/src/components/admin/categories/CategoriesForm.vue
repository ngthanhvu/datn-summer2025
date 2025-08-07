<template>
    <div
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 pt-6 pl-4 sm:pl-6 mx-auto gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Thêm danh mục mới</h1>
            <p class="text-gray-600 text-sm sm:text-base">Điền thông tin để tạo danh mục mới</p>
        </div>
    </div>
    <div
        class="bg-white p-4 sm:p-6 lg:p-10 w-full sm:w-[80%] lg:w-[60%] xl:w-[50%] mx-auto rounded-[10px] border border-gray-300">
        <div class="mb-4">
            <label class="block font-medium mb-1">Tên danh mục <span class="text-red-500">*</span></label>
            <input v-model="formData.name" type="text"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                placeholder="Nhập tên danh mục" />
            <div v-if="formErrors.name" class="text-red-500 text-sm">{{ formErrors.name }}</div>
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Mô tả <span class="text-red-500">*</span></label>
            <textarea v-model="formData.description"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100"
                placeholder="Nhập mô tả danh mục" rows="4"></textarea>
            <div v-if="formErrors.description" class="text-red-500 text-sm">{{ formErrors.description }}</div>
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">Danh mục cha</label>
            <select v-model="formData.parent_id"
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100">
                <option value="">Chọn danh mục cha</option>
                <option v-for="option in parentOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>
            <div v-if="formErrors.parent_id" class="text-red-500 text-sm">{{ formErrors.parent_id }}</div>
        </div>

        <div class="mb-4 flex items-center gap-2">
            <label class="block font-medium mb-1">Trạng thái</label>
            <button @click="formData.is_active = !formData.is_active" :class="[
                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                formData.is_active ? 'bg-[#3BB77E]' : 'bg-gray-200'
            ]">
                <span :class="[
                    'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                    formData.is_active ? 'translate-x-6' : 'translate-x-1'
                ]"></span>
            </button>
            <span class="text-sm sm:text-base">{{ formData.is_active ? 'Kích hoạt' : 'Ẩn' }}</span>
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1">
                Hình ảnh <span class="text-red-500">*</span>
            </label>

            <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-4 sm:p-6 text-center hover:border-primary cursor-pointer"
                @click="$refs.imageInput.click()">
                <input ref="imageInput" type="file" class="hidden" accept="image/png, image/jpeg, image/gif"
                    @change="onImageChange" />
                <div class="flex flex-col items-center">
                    <i class="fas fa-cloud-upload-alt text-2xl sm:text-3xl text-gray-400"></i>
                    <p class="mt-2 text-gray-600 text-sm sm:text-base">Click để tải ảnh lên</p>
                    <p class="text-xs text-gray-400">PNG, JPG, GIF (tối đa 2MB)</p>
                </div>
            </div>

            <div v-if="formErrors.image" class="text-red-500 text-sm mt-1">
                {{ formErrors.image }}
            </div>

            <div v-if="imagePreview" class="mt-4 relative inline-block">
                <img :src="imagePreview" alt="Preview" class="max-h-32 sm:max-h-40 rounded-lg shadow object-cover" />
                <button @click="removeImage"
                    class="absolute top-1 right-1 bg-white rounded-full p-1 shadow hover:bg-gray-100" title="Xóa ảnh">
                    <i class="fas fa-times text-red-500"></i>
                </button>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-end gap-3 sm:gap-4 mt-6">
            <router-link to="/admin/categories"
                class="px-4 py-2 border border-gray-300 rounded text-gray-600 hover:bg-gray-50 cursor-pointer text-center">
                Hủy
            </router-link>
            <button @click="handleSubmit"
                class="bg-primary text-white rounded px-4 py-2 hover:bg-primary-dark cursor-pointer">
                Tạo danh mục
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCategoryStore } from '../../../stores/categories'
import { useCategories } from '../../../composable/useCategories'
import { push } from 'notivue'

const { createCategory } = useCategories()
const router = useRouter()
const categoryStore = useCategoryStore()

const formData = ref({
    name: '',
    description: '',
    image: null,
    parent_id: '',
    is_active: true
})

const imageData = ref(null)
const imagePreview = ref(null)
const parentOptions = ref([])
const formErrors = ref({ name: '', description: '', parent_id: '', is_active: '', image: '' })

function onImageChange(e) {
    const file = e.target.files[0]
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            formErrors.value.image = 'Dung lượng ảnh tối đa 2MB'
            return
        }
        imageData.value = file
        imagePreview.value = URL.createObjectURL(file)
        formErrors.value.image = ''
    }
}

onMounted(async () => {
    try {
        await categoryStore.fetchCategories()
        parentOptions.value = categoryStore.categories.map(cat => ({
            value: cat.id,
            label: cat.name
        }))
    } catch (e) {
        console.error(e)
        parentOptions.value = []
    }
})

const validateForm = () => {
    const errors = { name: '', description: '', parent_id: '', image: '' }
    let hasError = false

    if (!formData.value.name) {
        errors.name = 'Vui lòng nhập tên danh mục'
        hasError = true
    } else if (formData.value.name.length < 3) {
        errors.name = 'Tên danh mục phải có ít nhất 3 ký tự'
        hasError = true
    }

    if (!formData.value.description) {
        errors.description = 'Vui lòng nhập mô tả danh mục'
        hasError = true
    }

    if (!imageData.value) {
        errors.image = 'Vui lòng chọn hình ảnh'
        hasError = true
    }

    formErrors.value = errors
    return !hasError
}

const handleSubmit = async () => {
    if (!validateForm()) return

    const formDataToSend = new FormData()
    formDataToSend.append('name', formData.value.name)
    formDataToSend.append('description', formData.value.description)
    formDataToSend.append('is_active', formData.value.is_active ? '1' : '0')
    if (formData.value.parent_id) formDataToSend.append('parent_id', formData.value.parent_id)

    if (imageData.value instanceof File) {
        formDataToSend.append('image', imageData.value)
    }

    try {
        await createCategory(formDataToSend)
        push.success('Tạo danh mục thành công')
        router.push('/admin/categories')
    } catch (error) {
        console.error('Error creating category:', error)
    }
}
</script>


<style scoped>
.bg-primary {
    background-color: #3bb77e;
}

.bg-primary-dark {
    background-color: #2ea16d;
}

.border-primary {
    border-color: #3bb77e;
}
</style>