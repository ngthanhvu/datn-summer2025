<template>
    <div class="categories-page">
        <div class="page-header">
            <h1>Quản lý danh mục</h1>
            <p class="text-gray-600">Quản lý danh mục sản phẩm của bạn</p>
        </div>

        <Table :columns="columns" :data="categories" @edit="handleEdit" @delete="handleDelete" @add="handleAdd" />

        <Modal :show="showModal" :title="editingCategory ? 'Chỉnh sửa danh mục' : 'Thêm danh mục mới'"
            @close="closeModal" @save="saveCategory">
            <Form :fields="formFields" :initial-data="formData" v-model="formData" @submit="saveCategory" />
        </Modal>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import { ref } from 'vue'
import Table from '~/components/admin/Table.vue'
import Modal from '~/components/admin/Modal.vue'
import Form from '~/components/admin/Form.vue'

// Table columns configuration
const columns = [
    { key: 'id', label: 'ID' },
    { key: 'icon', label: 'Icon' },
    { key: 'name', label: 'Tên danh mục' },
    { key: 'description', label: 'Mô tả' },
    { key: 'productCount', label: 'Số sản phẩm' },
    { key: 'status', label: 'Trạng thái', type: 'status' }
]

// Form fields configuration
const formFields = [
    {
        name: 'name',
        label: 'Tên danh mục',
        type: 'text',
        placeholder: 'Nhập tên danh mục',
        required: true
    },
    {
        name: 'description',
        label: 'Mô tả',
        type: 'textarea',
        placeholder: 'Nhập mô tả danh mục',
        rows: 4
    },
    {
        name: 'icon',
        label: 'Icon',
        type: 'text',
        placeholder: 'Nhập class icon (ví dụ: fas fa-mobile)'
    },
    {
        name: 'status',
        label: 'Trạng thái',
        type: 'toggle'
    }
]

// Mock data
const categories = ref([
    {
        id: 1,
        icon: 'fas fa-mobile',
        name: 'Điện thoại',
        description: 'Các loại điện thoại di động',
        productCount: 15,
        status: true
    },
    {
        id: 2,
        icon: 'fas fa-laptop',
        name: 'Laptop',
        description: 'Máy tính xách tay các loại',
        productCount: 10,
        status: true
    },
    {
        id: 3,
        icon: 'fas fa-tablet',
        name: 'Máy tính bảng',
        description: 'Các loại máy tính bảng',
        productCount: 8,
        status: true
    },
    {
        id: 4,
        icon: 'fas fa-headphones',
        name: 'Phụ kiện',
        description: 'Phụ kiện điện thoại, máy tính',
        productCount: 25,
        status: false
    }
])

// Modal state
const showModal = ref(false)
const editingCategory = ref(null)
const formData = ref({
    name: '',
    description: '',
    icon: '',
    status: true
})

// Handlers
const handleAdd = () => {
    editingCategory.value = null
    formData.value = {
        name: '',
        description: '',
        icon: '',
        status: true
    }
    showModal.value = true
}

const handleEdit = (category) => {
    editingCategory.value = category
    formData.value = { ...category }
    showModal.value = true
}

const handleDelete = (category) => {
    if (confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
        const index = categories.value.findIndex(c => c.id === category.id)
        if (index !== -1) {
            categories.value.splice(index, 1)
        }
    }
}

const closeModal = () => {
    showModal.value = false
    editingCategory.value = null
}

const saveCategory = () => {
    if (editingCategory.value) {
        // Edit existing category
        const index = categories.value.findIndex(c => c.id === editingCategory.value.id)
        if (index !== -1) {
            categories.value[index] = {
                ...formData.value,
                id: editingCategory.value.id,
                productCount: editingCategory.value.productCount
            }
        }
    } else {
        // Add new category
        const newCategory = {
            ...formData.value,
            id: categories.value.length + 1,
            productCount: 0
        }
        categories.value.push(newCategory)
    }
    closeModal()
}
</script>

<style scoped>
.categories-page {
    padding: 1.5rem;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.875rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.5rem;
}
</style>