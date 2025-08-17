<template>
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Quản lý liên hệ</h1>
            <p class="text-gray-600">Quản lý các liên hệ khách hàng gửi về hệ thống</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Tổng cộng</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Chưa trả lời</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.unreplied || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Đã trả lời</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.replied || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input v-model="searchQuery" @input="handleSearch" type="text"
                            placeholder="Tìm kiếm theo tên, email, số điện thoại..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="flex-shrink-0">
                    <select v-model="statusFilter" @change="handleFilter"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Tất cả trạng thái</option>
                        <option value="unreplied">Chưa trả lời</option>
                        <option value="replied">Đã trả lời</option>
                    </select>
                </div>

                <!-- Per Page -->
                <div class="flex-shrink-0">
                    <select v-model="perPage" @change="handlePerPageChange"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="15">15/trang</option>
                        <option value="25">25/trang</option>
                        <option value="50">50/trang</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- loading -->
        <div class="space-y-4">
            <div v-if="loading" class="bg-white rounded-xl shadow-sm p-8 text-center">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-gray-600">Đang tải danh sách liên hệ...</span>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="contacts.length === 0" class="bg-white rounded-xl shadow-sm p-8 text-center">
                <div class="flex flex-col items-center space-y-4">
                    <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                        </path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Không có liên hệ nào</h3>
                        <p class="text-gray-500">Chưa có khách hàng nào gửi liên hệ đến hệ thống</p>
                    </div>
                </div>
            </div>

            <!-- Contact Cards -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                <div v-for="contact in contacts" :key="contact.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 overflow-hidden">
                    <!-- Card Header -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ contact.name }}</h3>
                                <p class="text-sm text-gray-500">{{ contact.email }}</p>
                            </div>
                            <div class="flex flex-col items-end space-y-1">
                                <span v-if="contact.admin_reply"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Đã trả lời
                                </span>
                                <span v-else
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Chưa trả lời
                                </span>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                    </path>
                                </svg>
                                {{ contact.phone }}
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ formatDate(contact.created_at) }}
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6">
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Nội dung liên hệ:</h4>
                            <p class="text-sm text-gray-600 line-clamp-3 leading-relaxed">
                                {{ contact.message }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-2">
                            <button @click="openDetail(contact)"
                                class="flex-1 bg-blue-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                                <span class="flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                    {{ contact.admin_reply ? 'Xem/Chỉnh sửa' : 'Xem/Trả lời' }}
                                </span>
                            </button>

                            <button @click="confirmDelete(contact)"
                                class="bg-red-600 text-white px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200"
                                :disabled="deleteLoading === contact.id">
                                <span v-if="deleteLoading === contact.id" class="flex items-center">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </span>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="mt-8 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Hiển thị {{ (pagination.current_page - 1) * pagination.per_page + 1 }} đến
                {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
                trong tổng số {{ pagination.total }} liên hệ
            </div>

            <div class="flex items-center space-x-2">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                    class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Trước
                </button>

                <div class="flex items-center space-x-1">
                    <button v-for="page in getPageNumbers()" :key="page" @click="changePage(page)" :class="[
                        'px-3 py-2 text-sm font-medium rounded-lg',
                        page === pagination.current_page
                            ? 'bg-blue-600 text-white'
                            : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'
                    ]">
                        {{ page }}
                    </button>
                </div>

                <button @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Sau
                </button>
            </div>
        </div>

        <!-- Modal xem/trả lời -->
        <div v-if="selectedContact"
            class="fixed inset-0 bg-gray-900/75 bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="closeDetail">
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                    <h3 class="text-xl font-semibold text-gray-900">Chi tiết liên hệ</h3>
                    <button @click="closeDetail" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                    <!-- Contact Information -->
                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Thông tin khách hàng</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Họ tên</label>
                                <p class="text-gray-900 font-medium">{{ selectedContact.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                                <p class="text-gray-900">{{ selectedContact.email }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Số điện thoại</label>
                                <p class="text-gray-900">{{ selectedContact.phone }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Thời gian gửi</label>
                                <p class="text-gray-900">{{ formatDate(selectedContact.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Message -->
                    <div class="mb-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Nội dung liên hệ</h4>
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                            <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">{{
                                selectedContact.message }}</p>
                        </div>
                    </div>

                    <!-- Admin Reply Section -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Phản hồi</h4>

                        <!-- Existing Reply -->
                        <div v-if="selectedContact.admin_reply && String(selectedContact.admin_reply).trim() !== ''"
                            class="mb-4">
                            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-green-800">Đã phản hồi</span>
                                    <span class="text-sm text-green-600">{{ formatDate(selectedContact.replied_at)
                                        }}</span>
                                </div>
                                <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">{{
                                    selectedContact.admin_reply }}</p>
                            </div>

                            <!-- Edit Reply Button -->
                            <div class="mt-3 flex justify-end">
                                <button @click="enableEdit"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors">
                                    Chỉnh sửa phản hồi
                                </button>
                            </div>
                        </div>

                        <!-- Reply Form -->
                        <div
                            v-if="!selectedContact.admin_reply || String(selectedContact.admin_reply).trim() === '' || isEditing">
                            <form @submit.prevent="sendReply" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ isEditing ? 'Chỉnh sửa phản hồi' : 'Phản hồi cho khách hàng' }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <textarea v-model="reply" placeholder="Nhập nội dung phản hồi cho khách hàng..."
                                        class="w-full border border-gray-300 rounded-lg p-3 text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-y"
                                        rows="6" required></textarea>
                                    <p class="mt-1 text-xs text-gray-500">Phản hồi này sẽ được gửi qua email
                                        đến khách hàng</p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                                    <button v-if="isEditing" type="button" @click="cancelEdit"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                        Hủy
                                    </button>
                                    <button type="submit" :disabled="replyLoading || !reply.trim()"
                                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                        <span v-if="replyLoading" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                            Đang gửi...
                                        </span>
                                        <span v-else>{{ isEditing ? 'Cập nhật phản hồi' : 'Gửi phản hồi' }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useContact } from '../../../composable/useContact'
import Swal from 'sweetalert2'
import { push } from 'notivue'

const {
    fetchContacts,
    fetchContactDetail,
    replyContact,
    deleteContact,
    getContactStats,
    pagination
} = useContact()

const contacts = ref([])
const selectedContact = ref(null)
const reply = ref('')
const loading = ref(false)
const replyLoading = ref(false)
const deleteLoading = ref(null)
const isEditing = ref(false)
const stats = ref({})

// Search and filter states
const searchQuery = ref('')
const statusFilter = ref('')
const sortBy = ref('created_at')
const sortOrder = ref('desc')
const perPage = ref(15)
const currentPage = ref(1)

let searchTimeout = null

const loadContacts = async () => {
    loading.value = true
    try {
        const params = {
            search: searchQuery.value,
            status: statusFilter.value,
            sort_by: sortBy.value,
            sort_order: sortOrder.value,
            per_page: perPage.value,
            page: currentPage.value
        }
        const result = await fetchContacts(params)
        if (result && result.data) {
            contacts.value = result.data
        }
    } catch (error) {
        console.error('Error loading contacts:', error)
    } finally {
        loading.value = false
    }
}

const loadStats = async () => {
    try {
        stats.value = await getContactStats()
    } catch (error) {
        console.error('Error loading stats:', error)
    }
}

const handleSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        currentPage.value = 1
        loadContacts()
    }, 500)
}

const handleFilter = () => {
    currentPage.value = 1
    loadContacts()
}

const handleSort = () => {
    currentPage.value = 1
    loadContacts()
}

const handlePerPageChange = () => {
    currentPage.value = 1
    loadContacts()
}

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        currentPage.value = page
        loadContacts()
    }
}

const getPageNumbers = () => {
    const pages = []
    const current = pagination.value.current_page
    const last = pagination.value.last_page

    if (last <= 7) {
        for (let i = 1; i <= last; i++) {
            pages.push(i)
        }
    } else {
        if (current <= 4) {
            for (let i = 1; i <= 5; i++) {
                pages.push(i)
            }
            pages.push('...')
            pages.push(last)
        } else if (current >= last - 3) {
            pages.push(1)
            pages.push('...')
            for (let i = last - 4; i <= last; i++) {
                pages.push(i)
            }
        } else {
            pages.push(1)
            pages.push('...')
            for (let i = current - 1; i <= current + 1; i++) {
                pages.push(i)
            }
            pages.push('...')
            pages.push(last)
        }
    }

    return pages.filter(page => page !== '...')
}

const openDetail = async (contact) => {
    try {
        let detail = await fetchContactDetail(contact.id)
        if (detail && detail.data) detail = detail.data
        selectedContact.value = detail
        reply.value = detail.admin_reply || ''
        isEditing.value = false
    } catch (error) {
        Swal.fire('Lỗi', 'Không thể lấy chi tiết liên hệ', 'error')
    }
}

const closeDetail = () => {
    selectedContact.value = null
    reply.value = ''
    isEditing.value = false
}

const enableEdit = () => {
    isEditing.value = true
    reply.value = selectedContact.value.admin_reply || ''
}

const cancelEdit = () => {
    isEditing.value = false
    reply.value = selectedContact.value.admin_reply || ''
}

const sendReply = async () => {
    if (!reply.value.trim()) {
        push.error('Vui lòng nhập nội dung phản hồi')
        return
    }

    replyLoading.value = true
    try {
        await replyContact(selectedContact.value.id, reply.value)

        const message = isEditing.value ? 'Đã cập nhật phản hồi!' : 'Đã gửi phản hồi!'
        push.success(message)

        closeDetail()
        await loadContacts()
        await loadStats()
    } catch (err) {
        Swal.fire('Lỗi', err.response?.data?.message || 'Gửi phản hồi thất bại', 'error')
    } finally {
        replyLoading.value = false
    }
}

const confirmDelete = async (contact) => {
    const result = await Swal.fire({
        title: 'Xác nhận xóa?',
        text: `Bạn có chắc chắn muốn xóa liên hệ từ "${contact.name}"? Hành động này không thể hoàn tác.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    })

    if (result.isConfirmed) {
        await deleteContactItem(contact)
    }
}

const deleteContactItem = async (contact) => {
    deleteLoading.value = contact.id
    try {
        await deleteContact(contact.id)
        await Swal.fire('Thành công', 'Đã xóa liên hệ thành công!', 'success')
        await loadContacts()
        await loadStats()
    } catch (err) {
        Swal.fire('Lỗi', err.response?.data?.message || 'Xóa liên hệ thất bại', 'error')
    } finally {
        deleteLoading.value = null
    }
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    try {
        return new Date(dateString).toLocaleString('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        })
    } catch (error) {
        return dateString
    }
}

onMounted(async () => {
    await loadStats()
    await loadContacts()
})
</script>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    line-clamp: 3;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>