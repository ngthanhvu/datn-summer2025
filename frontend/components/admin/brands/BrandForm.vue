<template>
    <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">{{ title }}</h1>
                <p class="tw-text-gray-600">{{ description }}</p>
            </div>
            <NuxtLink to="/admin/brands"
                class="tw-bg-gray-100 tw-text-gray-600 tw-rounded tw-px-4 tw-py-2 tw-flex tw-items-center tw-gap-2 hover:tw-bg-gray-200">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </NuxtLink>
        </div>

        <Form :fields="formFields" v-model="formData" @submit="handleSubmit" />

        <div class="tw-mt-6">
            <ImageUpload v-model="imageData" label="Logo thương hiệu" required />
        </div>

        <div class="tw-flex tw-justify-end tw-gap-4 tw-mt-6">
            <NuxtLink to="/admin/brands"
                class="tw-px-4 tw-py-2 tw-border tw-rounded tw-text-gray-600 hover:tw-bg-gray-50">
                Hủy
            </NuxtLink>
            <button @click="handleSubmit"
                class="tw-bg-primary tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-primary-dark">
                {{ submitText }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Form from '~/components/admin/Form.vue'
import ImageUpload from '~/components/admin/ImageUpload.vue'

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        required: true
    },
    submitText: {
        type: String,
        required: true
    },
    initialData: {
        type: Object,
        default: () => ({
            name: '',
            description: '',
            image: null,
            parent_id: '',
            is_active: true
        })
    }
})

const emit = defineEmits(['submit'])

const formData = ref({ ...props.initialData })
const imageData = ref(null)

const formFields = ref([
    {
        name: 'name',
        label: 'Tên thương hiệu',
        type: 'text',
        placeholder: 'Nhập tên thương hiệu',
        required: true
    },
    {
        name: 'description',
        label: 'Mô tả',
        type: 'textarea',
        placeholder: 'Nhập mô tả thương hiệu',
        rows: 4
    },
    {
        name: 'parent_id',
        label: 'Thương hiệu cha',
        type: 'select',
        placeholder: 'Chọn thương hiệu cha',
        options: [],
        clearable: true
    },
    {
        name: 'is_active',
        label: 'Trạng thái',
        type: 'toggle',
        value: true
    }
])

const handleSubmit = async () => {
    if (!formData.value.name) {
        alert('Vui lòng nhập tên thương hiệu')
        return
    }

    if (!imageData.value) {
        alert('Vui lòng chọn hình ảnh')
        return
    }

    const formDataToSend = new FormData()
    formDataToSend.append('name', formData.value.name)
    formDataToSend.append('description', formData.value.description || '')

    const isActive = formData.value.is_active === undefined ? true : Boolean(formData.value.is_active)
    formDataToSend.append('is_active', isActive ? '1' : '0')

    const parentId = formData.value.parent_id
    if (parentId && parentId !== '') {
        formDataToSend.append('parent_id', parentId.toString())
    }

    if (imageData.value instanceof File) {
        formDataToSend.append('image', imageData.value)
    } else if (typeof imageData.value === 'string' && imageData.value.startsWith('data:')) {
        const response = await fetch(imageData.value)
        const blob = await response.blob()
        formDataToSend.append('image', blob, 'image.jpg')
    } else if (typeof imageData.value === 'string') {
        formDataToSend.append('image', imageData.value)
    }

    emit('submit', formDataToSend)
}

defineExpose({
    formFields
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