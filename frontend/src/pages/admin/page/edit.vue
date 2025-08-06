<template>
  <div class="p-6 md:p-10 bg-gray-50 min-h-screen">
    <!-- Top Bar -->
    <div class="flex justify-between items-center mb-8">
      <div class="relative max-w-xs w-full">
        
      </div>
      <button @click="$router.go(-1)" class="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
        <i class="fas fa-arrow-left"></i> Quay lại
      </button>
    </div>

    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-1">Chỉnh sửa trang</h1>
      <p class="text-gray-600">Cập nhật thông tin trang</p>
    </div>

    <div v-if="loading" class="flex flex-col items-center justify-center py-16">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500 mb-4"></div>
      <p class="text-gray-500">Đang tải thông tin trang...</p>
    </div>
    <form v-else @submit.prevent="handleSubmit" class="space-y-8">
      <!-- 2-column grid for info and SEO -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Thông tin trang -->
        <div class="bg-white rounded-2xl shadow p-8 flex flex-col gap-4">
          <h3 class="text-lg font-semibold mb-4">Thông tin trang</h3>
          <div>
            <label for="title" class="block text-sm font-medium mb-1">Tên trang *</label>
            <input id="title" v-model="formData.title" type="text" class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-green-100 focus:border-green-500" placeholder="Nhập tên trang" required />
            <span v-if="errors.title" class="text-red-500 text-xs mt-1">{{ errors.title }}</span>
          </div>
          <div>
            <label for="type" class="block text-sm font-medium mb-1">Loại trang *</label>
            <select id="type" v-model="formData.type" class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-green-100 focus:border-green-500" required>
              <option value="">Chọn loại trang</option>
              <option value="policy">Chính sách</option>
              <option value="support">Hỗ trợ khách hàng</option>
              <option value="other">Khác</option>
            </select>
            <span v-if="errors.type" class="text-red-500 text-xs mt-1">{{ errors.type }}</span>
          </div>
          <div>
            <label for="sort_order" class="block text-sm font-medium mb-1">Thứ tự hiển thị</label>
            <input id="sort_order" v-model.number="formData.sort_order" type="number" min="0" class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-green-100 focus:border-green-500" placeholder="0" />
            <span v-if="errors.sort_order" class="text-red-500 text-xs mt-1">{{ errors.sort_order }}</span>
          </div>
          <div>
            <label for="status" class="block text-sm font-medium mb-1">Hiển thị</label>
            <div class="flex items-center gap-3 mt-2">
              <!-- Custom switch -->
              <button type="button" @click="formData.status = !formData.status" :aria-pressed="formData.status"
                class="relative inline-flex h-6 w-12 rounded-full transition-colors duration-200 focus:outline-none border-2 border-gray-300"
                :class="formData.status ? 'bg-green-500 border-green-500' : 'bg-gray-200 border-gray-300'">
                <span class="sr-only">Toggle status</span>
                <span class="inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition duration-200"
                  :class="formData.status ? 'translate-x-6' : 'translate-x-1'">
                </span>
              </button>
              <span class="text-sm font-medium select-none" :class="formData.status ? 'text-green-600' : 'text-gray-400'">
                {{ formData.status ? 'Bật' : 'Tắt' }}
              </span>
            </div>
          </div>
        </div>
        <!-- SEO & Meta -->
        <div class="bg-white rounded-2xl shadow p-8 flex flex-col gap-4">
          <h3 class="text-lg font-semibold mb-4">SEO & Meta</h3>
          <div>
            <label for="meta_title" class="block text-sm font-medium mb-1">Meta Title</label>
            <input id="meta_title" v-model="formData.meta_title" type="text" class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-green-100 focus:border-green-500" placeholder="Meta title cho SEO..." />
            <span v-if="errors.meta_title" class="text-red-500 text-xs mt-1">{{ errors.meta_title }}</span>
          </div>
          <div>
            <label for="meta_description" class="block text-sm font-medium mb-1">Meta Description</label>
            <textarea id="meta_description" v-model="formData.meta_description" class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-green-100 focus:border-green-500" rows="4" placeholder="Meta description cho SEO..."></textarea>
            <span v-if="errors.meta_description" class="text-red-500 text-xs mt-1">{{ errors.meta_description }}</span>
          </div>
        </div>
      </div>
      <!-- Full-width for content -->
      <div class="bg-white rounded-2xl shadow p-8">
        <h3 class="text-lg font-semibold mb-4">Nội dung trang *</h3>
        <div>
          <CKEditor v-model="formData.content" />
          <span v-if="errors.content" class="text-red-500 text-xs mt-1">{{ errors.content }}</span>
        </div>
      </div>
      <!-- Action Buttons -->
      <div class="flex justify-end gap-4 pt-4 border-t mt-4">
        <button type="button" @click="$router.go(-1)" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Hủy</button>
        <button type="submit" :disabled="submitting" class="bg-green-500 text-white rounded px-4 py-2 hover:bg-green-600 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
          <div v-if="submitting" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
          Cập nhật trang
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { usePages } from '../../../composable/usePages'
import { useHead } from '@vueuse/head';
import { push } from 'notivue'
import CKEditor from '../../../components/CKEditor.vue'
useHead({
    title: "Sửa trang page | DEVGANG",
    meta: [
        {
            name: "description",
            content: "Sửa trang"
        }
    ]
})

const router = useRouter()
const route = useRoute()
const { fetchPage, updatePage, loading, page } = usePages()

const contentEditor = ref(null)
const isBold = ref(false)
const isItalic = ref(false)
const isUnderline = ref(false)
const submitting = ref(false)

const formData = ref({
    title: '',
    type: '',
    content: '',
    meta_title: '',
    meta_description: '',
    status: true,
    sort_order: 0
})

const errors = ref({})

const validateForm = () => {
    const err = {}
    if (!formData.value.title) err.title = 'Vui lòng nhập tiêu đề trang.'
    if (!formData.value.type) err.type = 'Vui lòng chọn loại trang.'
    if (!formData.value.content) err.content = 'Vui lòng nhập nội dung trang.'
    if (formData.value.sort_order < 0) err.sort_order = 'Thứ tự hiển thị phải lớn hơn hoặc bằng 0.'
    errors.value = err
    return Object.keys(err).length === 0
}

const loadPage = async () => {
    try {
        const pageId = route.params.id
        await fetchPage(pageId)
        if (page.value) {
            formData.value = {
                title: page.value.title || '',
                type: page.value.type || '',
                content: page.value.content || '',
                meta_title: page.value.meta_title || '',
                meta_description: page.value.meta_description || '',
                status: page.value.status !== undefined ? page.value.status : true,
                sort_order: page.value.sort_order || 0
            }
        }
    } catch (error) {
        console.error('Error loading page:', error)
        push.error('Có lỗi xảy ra khi tải thông tin trang!')
    }
}

const handleSubmit = async () => {
    if (!validateForm()) return

    submitting.value = true
    try {
        const pageId = route.params.id
        await updatePage(pageId, formData.value)
        push.success('Cập nhật trang thành công!')
        router.push('/admin/pages')
    } catch (error) {
        console.error('Error updating page:', error)
        push.error('Có lỗi xảy ra khi cập nhật trang!')
    } finally {
        submitting.value = false
    }
}

const formatText = (command) => {
    const textarea = contentEditor.value
    const start = textarea.selectionStart
    const end = textarea.selectionEnd
    const selectedText = textarea.value.substring(start, end)

    let replacement = ''
    switch (command) {
        case 'bold':
            replacement = `**${selectedText}**`
            break
        case 'italic':
            replacement = `*${selectedText}*`
            break
        case 'underline':
            replacement = `__${selectedText}__`
            break
    }

    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end)
    textarea.focus()
    textarea.setSelectionRange(start + replacement.length, start + replacement.length)
}

const insertList = (type) => {
    const textarea = contentEditor.value
    const start = textarea.selectionStart
    const prefix = type === 'ul' ? '- ' : '1. '

    textarea.value = textarea.value.substring(0, start) + prefix + textarea.value.substring(start)
    textarea.focus()
    textarea.setSelectionRange(start + prefix.length, start + prefix.length)
}

const insertLink = () => {
    const textarea = contentEditor.value
    const start = textarea.selectionStart
    const end = textarea.selectionEnd
    const selectedText = textarea.value.substring(start, end)

    const linkText = selectedText || 'Link text'
    const replacement = `[${linkText}](URL)`

    textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end)
    textarea.focus()
    textarea.setSelectionRange(start + replacement.length, start + replacement.length)
}

onMounted(() => {
    loadPage()
})
</script>