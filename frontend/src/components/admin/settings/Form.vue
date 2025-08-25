<template>
    <form @submit.prevent="handleSubmit" class="form">
        <div v-for="field in fields" :key="field.name" class="form-group">
            <label :for="field.name">{{ field.label }} <span class="text-red-500">*</span></label>

            <!-- Text Input -->
            <input v-if="field.type === 'text'" :id="field.name" v-model="formData[field.name]" type="text"
                :placeholder="field.placeholder" />

            <!-- Number Input -->
            <input v-else-if="field.type === 'number'" :id="field.name" v-model.number="formData[field.name]"
                type="number" :min="field.min" :max="field.max" :step="field.step" :placeholder="field.placeholder" />

            <!-- Textarea -->
            <textarea v-else-if="field.type === 'textarea'" :id="field.name" v-model="formData[field.name]"
                :placeholder="field.placeholder" :rows="field.rows || 4"></textarea>

            <!-- Select -->
            <select v-else-if="field.type === 'select'" :id="field.name" v-model="formData[field.name]">
                <option v-if="field.placeholder" value="">{{ field.placeholder }}</option>
                <option v-for="option in field.options" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>

            <!-- Toggle/Switch -->
            <div v-else-if="field.type === 'toggle'" class="toggle">
                <input type="checkbox" :id="field.name" v-model="formData[field.name]" />
                <label :for="field.name"></label>
            </div>
            <!-- Image (Single) đẹp -->
            <div v-else-if="field.type === 'image'" class="mb-4">

                <!-- Có ảnh thì hiển thị preview -->
                <div v-if="formData[field.name]" class="relative inline-block mb-2">
                    <img :src="formData[field.name]" :alt="field.label"
                        class="max-h-24 sm:max-h-32 rounded-md border border-gray-300" />
                    <button type="button" @click="removeImage(field.name)"
                        class="absolute top-1 right-1 bg-white text-red-600 text-xs px-2 py-1 rounded hover:bg-red-100">
                        Xoá ảnh
                    </button>
                </div>

                <!-- Nếu chưa có ảnh -->
                <label v-else :for="field.name"
                    class="block border-2 border-dashed border-gray-300 rounded-md py-6 sm:py-10 text-center cursor-pointer hover:bg-gray-50">
                    <div class="text-gray-400 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 sm:w-8 sm:h-8 mx-auto" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v9m0 0l-3-3m3 3l3-3m6-3V5a2 2 0 00-2-2H6a2 2 0 00-2 2v11" />
                        </svg>
                    </div>
                    <p class="text-gray-600 text-sm sm:text-base">Click để tải ảnh lên</p>
                    <p class="text-gray-400 text-xs">PNG, JPG, GIF (tối đa 2MB)</p>
                </label>

                <!-- File input -->
                <input type="file" class="hidden" :id="field.name" :name="field.name" accept="image/*"
                    @change="(e) => handleImageUpload(e, field.name)" />
            </div>

            <!-- Multiple Images (for banners) -->
            <div v-else-if="field.type === 'images'" class="space-y-4">
                <div v-if="field.description" class="text-sm text-gray-600 font-medium">{{ field.description }}</div>

                <!-- Image Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4">
                    <!-- Existing Images -->
                    <div v-for="(image, index) in getImagesArray(field.name)" :key="index"
                        class="group relative aspect-square overflow-hidden rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                        <img :src="image" :alt="`${field.label} ${index + 1}`"
                            class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105"
                            @error="handleImageError" />

                        <!-- Overlay on hover -->
                        <div
                            class="absolute inset-0 bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200">
                        </div>

                        <!-- Remove button -->
                        <button type="button" @click="removeMultipleImage(field.name, index)"
                            class="absolute top-1 sm:top-2 right-1 sm:right-2 w-6 h-6 sm:w-7 sm:h-7 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg transition-all duration-200 opacity-0 group-hover:opacity-100 hover:scale-110">
                            ×
                        </button>
                    </div>

                    <!-- Add More Button -->
                    <label :for="`${field.name}_upload`"
                        class="aspect-square border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 group">
                        <div
                            class="text-gray-400 group-hover:text-blue-500 mb-1 sm:mb-2 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 sm:w-8 sm:h-8" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <p
                            class="text-gray-600 group-hover:text-blue-600 text-xs sm:text-sm font-medium transition-colors duration-200">
                            Thêm ảnh</p>
                        <p class="text-gray-400 text-xs mt-1">PNG, JPG, GIF</p>
                    </label>
                </div>

                <!-- File input -->
                <input type="file" class="hidden" :id="`${field.name}_upload`" multiple accept="image/*"
                    @change="(e) => handleMultipleImageUpload(e, field.name)" />
            </div>

            <!-- Password Input -->
            <input v-else-if="field.type === 'password'" :id="field.name" v-model="formData[field.name]" type="password"
                :placeholder="field.placeholder"
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none" />

            <!-- Main Image Upload -->
            <div v-else-if="field.type === 'mainImage'" class="image-upload">
                <div v-if="field.description" class="text-sm text-gray-500 mb-2">{{ field.description }}</div>
                <div class="image-preview" v-if="formData.mainImagePreview">
                    <img :src="formData.mainImagePreview" :alt="field.label">
                    <button type="button" @click="removeMainImage" class="remove-image">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div v-else class="upload-placeholder" @click="triggerMainImageUpload">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Thêm ảnh chính</span>
                </div>
                <input type="file" id="mainImage" ref="mainImageInput" @change="handleMainImageUpload" accept="image/*"
                    class="hidden" />
            </div>

            <!-- Additional Images Upload -->
            <div v-else-if="field.type === 'additionalImages'" class="additional-images-upload">
                <div v-if="field.description" class="text-sm text-gray-500 mb-2">{{ field.description }}</div>
                <div class="image-grid">
                    <div v-for="(preview, index) in formData.additionalImagePreviews" :key="index"
                        class="image-preview">
                        <img :src="preview" :alt="`Additional Image ${index + 1}`">
                        <button type="button" @click="removeAdditionalImage(index)" class="remove-image">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="upload-placeholder" @click="triggerAdditionalImagesUpload">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Thêm ảnh phụ</span>
                    </div>
                </div>
                <input type="file" id="additionalImages" ref="additionalImagesInput"
                    @change="handleAdditionalImagesUpload" accept="image/*" multiple class="hidden" />
            </div>

            <!-- Error Message -->
            <span v-if="localErrors[field.name]" class="error-message">
                {{ localErrors[field.name] }}
            </span>
        </div>
    </form>
</template>

<script setup>
import { ref, watch } from 'vue'
import { isEqual } from 'lodash' // Requires npm install lodash

const props = defineProps({
    fields: {
        type: Array,
        required: true
    },
    initialData: {
        type: Object,
        default: () => ({})
    },
    errors: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['submit', 'update:modelValue'])

const formData = ref({ ...props.initialData })
const localErrors = ref({})

// Watch for changes in initialData
watch(() => props.initialData, (newVal) => {
    if (!isEqual(newVal, formData.value)) {
        formData.value = { ...newVal }
    }
}, { deep: true })

// Watch for form changes
watch(formData, (newVal) => {
    if (!isEqual(newVal, props.initialData)) {
        emit('update:modelValue', { ...newVal })
    }
}, { deep: true })

// Watch for external errors
watch(() => props.errors, (newErrors) => {
    localErrors.value = { ...newErrors }
}, { deep: true })

const validateField = (field, value) => {
    if (!field.validation) return ''

    const validation = field.validation

    if (validation.required && !value) {
        return validation.required
    }

    if (validation.minLength && value && value.length < validation.minLength.value) {
        return validation.minLength.message
    }

    if (validation.min && value < validation.min.value) {
        return validation.min.message
    }

    if (validation.pattern && value && !validation.pattern.value.test(value)) {
        return validation.pattern.message
    }

    return ''
}

const handleSubmit = () => {
    localErrors.value = {}
    let hasError = false

    props.fields.forEach(field => {
        const error = validateField(field, formData.value[field.name])
        if (error) {
            localErrors.value[field.name] = error
            hasError = true
        }
    })

    if (!hasError) {
        emit('submit', formData.value)
    }
}

// Image upload handling
const triggerImageUpload = (fieldName) => {
    document.getElementById(fieldName).click()
}

const handleImageUpload = (event, fieldName) => {
    const file = event.target.files[0]
    if (file) {
        const reader = new FileReader()
        reader.onload = (e) => {
            formData.value = { ...formData.value, [fieldName]: e.target.result }
        }
        reader.readAsDataURL(file)
    }
}

const removeImage = (fieldName) => {
    formData.value = { ...formData.value, [fieldName]: null }
}

const handleMainImageUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        const reader = new FileReader()
        reader.onload = (e) => {
            formData.value = {
                ...formData.value,
                mainImage: file,
                mainImagePreview: e.target.result
            }
        }
        reader.readAsDataURL(file)
    }
}

const removeMainImage = () => {
    formData.value = {
        ...formData.value,
        mainImage: null,
        mainImagePreview: null
    }
}

const triggerMainImageUpload = () => {
    document.getElementById('mainImage').click()
}

const handleAdditionalImagesUpload = async (event) => {
    const files = Array.from(event.target.files)
    const newAdditionalImages = [...formData.value.additionalImages]
    const newAdditionalImagePreviews = [...formData.value.additionalImagePreviews]
    const processFile = (file) => {
        return new Promise((resolve) => {
            const reader = new FileReader()
            reader.onload = (e) => {
                resolve(e.target.result)
            }
            reader.readAsDataURL(file)
        })
    }
    const previews = await Promise.all(files.map(processFile))
    newAdditionalImages.push(...files)
    newAdditionalImagePreviews.push(...previews)
    formData.value = {
        ...formData.value,
        additionalImages: newAdditionalImages,
        additionalImagePreviews: newAdditionalImagePreviews
    }
}

const removeAdditionalImage = (index) => {
    const newAdditionalImages = [...formData.value.additionalImages]
    const newAdditionalImagePreviews = [...formData.value.additionalImagePreviews]
    newAdditionalImages.splice(index, 1)
    newAdditionalImagePreviews.splice(index, 1)
    formData.value = {
        ...formData.value,
        additionalImages: newAdditionalImages,
        additionalImagePreviews: newAdditionalImagePreviews
    }
}

const triggerAdditionalImagesUpload = () => {
    document.getElementById('additionalImages').click()
}

const getImagesArray = (fieldName) => {
    const value = formData.value[fieldName]

    if (!value) return []

    // If it's already an array, return it
    if (Array.isArray(value)) {
        return value
    }

    // If it's a string (maybe JSON), try to parse it
    if (typeof value === 'string') {
        try {
            const parsed = JSON.parse(value)
            if (Array.isArray(parsed)) {
                return parsed
            } else {
                return [value]
            }
        } catch {
            // If not JSON, treat as single image
            return [value]
        }
    }

    return []
}

const handleMultipleImageUpload = async (event, fieldName) => {
    const files = Array.from(event.target.files)
    const existingImages = getImagesArray(fieldName)

    const processFile = (file) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader()
            reader.onload = (e) => {
                resolve(e.target.result)
            }
            reader.onerror = (error) => {
                reject(error)
            }
            reader.readAsDataURL(file)
        })
    }

    const newImages = await Promise.all(files.map(processFile))
    const updatedImages = [...existingImages, ...newImages]

    formData.value = {
        ...formData.value,
        [fieldName]: updatedImages
    }

    // Reset input
    event.target.value = ''
}

const removeMultipleImage = (fieldName, index) => {
    const existingImages = getImagesArray(fieldName)
    const updatedImages = existingImages.filter((_, i) => i !== index)

    formData.value = {
        ...formData.value,
        [fieldName]: updatedImages
    }
}

// Error handling for images
const handleImageError = (event) => {
    // Fallback to a default image or show an error message
    event.target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgdmlld0JveD0iMCAwIDE1MCAxNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxNTAiIGhlaWdodD0iMTUwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik03NSA0MEg2NVY1MEg3NVY0MFoiIGZpbGw9IiM5Q0EzQUYiLz4KPHBhdGggZD0iTTc1IDUwSDY1VjYwSDc1VjUwWiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNNzUgNjBINjVWNzBINzVWNjBaIiBmaWxsPSIjOUNBM0FGIi8+CjxwYXRoIGQ9Ik04NSA0MEg3NVY1MEg4NVY0MFoiIGZpbGw9IiM5Q0EzQUYiLz4KPHBhdGggZD0iTTg1IDUwSDc1VjYwSDg1VjUwWiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNODUgNjBINzVWNzBIODVWNjBaIiBmaWxsPSIjOUNBM0FGIi8+Cjx0ZXh0IHg9Ijc1IiB5PSI5MCIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjEyIiBmaWxsPSIjNjc3NDhEIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj7huqNuIGluaCBuaMOgbjwvdGV4dD4KPC9zdmc+'
}
</script>

<style scoped>
.form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 500;
    color: #374151;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.875rem;
}

.form-group textarea {
    resize: vertical;
}

.toggle {
    position: relative;
    display: inline-block;
}

.toggle input {
    display: none;
}

.toggle label {
    display: block;
    width: 48px;
    height: 24px;
    background: #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.3s;
}

.toggle label::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    transition: transform 0.3s;
}

.toggle input:checked+label {
    background: #3bb77e;
}

.toggle input:checked+label::after {
    transform: translateX(24px);
}

.error-message {
    color: #ef4444;
    font-size: 0.875rem;
}

/* Ensure images display properly */
img {
    display: block;
    max-width: 100%;
    height: auto;
}

/* Fix for aspect-square images */
.aspect-square img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Mobile optimizations */
@media (max-width: 640px) {

    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group select,
    .form-group textarea {
        font-size: 16px;
        /* Prevents zoom on iOS */
    }

    .form-group label {
        font-size: 0.875rem;
    }
}
</style>