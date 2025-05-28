<template>
    <form @submit.prevent="handleSubmit" class="form">
        <div v-for="field in fields" :key="field.name" class="form-group">
            <label :for="field.name">{{ field.label }}</label>

            <!-- Text Input -->
            <input v-if="field.type === 'text'" :id="field.name" v-model="formData[field.name]" type="text"
                :placeholder="field.placeholder" />

            <!-- Number Input -->
            <input v-else-if="field.type === 'number'" :id="field.name" v-model="formData[field.name]" type="number"
                :min="field.min" :max="field.max" :step="field.step" :placeholder="field.placeholder" />

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

            <!-- Image Upload -->
            <div v-else-if="field.type === 'image'" class="image-upload">
                <div class="image-preview" v-if="formData[field.name]">
                    <img :src="formData[field.name]" :alt="field.label">
                    <button type="button" @click="removeImage(field.name)" class="remove-image">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div v-else class="upload-placeholder" @click="triggerImageUpload(field.name)">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Click để tải ảnh lên</span>
                </div>
                <input type="file" :id="field.name" :ref="field.name" @change="handleImageUpload($event, field.name)"
                    accept="image/*" class="hidden" />
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
    formData.value = { ...newVal }
}, { deep: true })

// Watch for form changes
watch(formData, (newVal) => {
    emit('update:modelValue', newVal)
}, { deep: true })

const handleSubmit = () => {
    // Reset errors
    errors.value = {}

    // Validate required fields
    props.fields.forEach(field => {
        if (field.required && !formData.value[field.name]) {
            errors.value[field.name] = `${field.label} là bắt buộc`
        }
    })

    // If no errors, emit submit event
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
            formData.value[fieldName] = e.target.result
        }
        reader.readAsDataURL(file)
    }
}

const removeImage = (fieldName) => {
    formData.value[fieldName] = null
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

/* Toggle styles */
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

/* Image upload styles */
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
    width: 150px;
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

/* Error message */
.error-message {
    color: #ef4444;
    font-size: 0.875rem;
}
</style>