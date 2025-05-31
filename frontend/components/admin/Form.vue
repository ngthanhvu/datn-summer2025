<template>
    <form @submit.prevent="handleSubmit" class="form">
        <div v-for="field in fields" :key="field.name" class="form-group">
            <label :for="field.name">{{ field.label }}</label>

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

            <!-- Main Image Upload -->
            <div v-else-if="field.type === 'mainImage'" class="image-upload">
                <div v-if="field.description" class="tw-text-sm tw-text-gray-500 tw-mb-2">{{ field.description }}</div>
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
                <div v-if="field.description" class="tw-text-sm tw-text-gray-500 tw-mb-2">{{ field.description }}</div>
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
            <span v-if="errors[field.name]" class="error-message">
                {{ errors[field.name] }}
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
    }
})

const emit = defineEmits(['submit', 'update:modelValue'])

const formData = ref({ ...props.initialData })
const errors = ref({})

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

const handleSubmit = () => {
    errors.value = {}
    props.fields.forEach(field => {
        if (field.required && !formData.value[field.name]) {
            errors.value[field.name] = `${field.label} là bắt buộc`
        }
    })
    if (Object.keys(errors.value).length === 0) {
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

.image-upload {
    width: 100%;
}

.image-preview {
    position: relative;
    width: 150px;
    height: 150px;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}

.remove-image {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #ef4444;
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.upload-placeholder {
    height: 150px;
    border: 2px dashed #e5e7eb;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: pointer;
    color: #6b7280;
}

.upload-placeholder i {
    font-size: 2rem;
}

.hidden {
    display: none;
}

.error-message {
    color: #ef4444;
    font-size: 0.875rem;
}

.additional-images-upload {
    width: 100%;
}

.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 0.5rem;
}

.image-grid .image-preview,
.image-grid .upload-placeholder {
    aspect-ratio: 1;
    width: 100%;
}

.image-grid .upload-placeholder {
    height: auto;
}
</style>