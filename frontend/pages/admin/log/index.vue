<template>
    <div class="logs-page">
        <div class="page-header tw-flex tw-justify-between tw-items-center tw-mb-6">
            <div>
                <h1 class="tw-text-2xl tw-font-semibold tw-text-gray-900">Nhật ký hoạt động</h1>
                <p class="tw-text-gray-600">Theo dõi các hoạt động trong hệ thống</p>
            </div>
        </div>

        <div class="tw-bg-white tw-rounded-lg tw-shadow">
            <!-- Filter Section -->
            <div class="tw-p-4 tw-border-b">
                <div class="tw-flex tw-gap-4">
                    <div class="tw-relative">
                        <input type="text" v-model="searchQuery" placeholder="Tìm kiếm..."
                            class="tw-border tw-rounded tw-px-4 tw-py-2 tw-pl-10 tw-w-64">
                        <i
                            class="fas fa-search tw-absolute tw-left-3 tw-top-1/2 tw-transform -tw-translate-y-1/2 tw-text-gray-400"></i>
                    </div>
                    <select v-model="filterType" class="tw-border tw-rounded tw-px-4 tw-py-2">
                        <option value="">Tất cả hoạt động</option>
                        <option value="order">Đơn hàng</option>
                        <option value="product">Sản phẩm</option>
                        <option value="user">Người dùng</option>
                        <option value="system">Hệ thống</option>
                    </select>
                    <div class="tw-flex tw-items-center tw-gap-2">
                        <input type="date" v-model="startDate" class="tw-border tw-rounded tw-px-4 tw-py-2">
                        <span>đến</span>
                        <input type="date" v-model="endDate" class="tw-border tw-rounded tw-px-4 tw-py-2">
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="tw-p-4">
                <div class="timeline">
                    <div v-for="(group, date) in groupedLogs" :key="date" class="timeline-group">
                        <div class="timeline-date">{{ formatDate(date) }}</div>
                        <div class="timeline-items">
                            <div v-for="log in group" :key="log.id" class="timeline-item"
                                :class="getLogTypeClass(log.type)">
                                <div class="timeline-icon">
                                    <i :class="getLogTypeIcon(log.type)"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="tw-flex tw-justify-between tw-items-start">
                                        <div>
                                            <p class="tw-font-medium">{{ log.message }}</p>
                                            <p class="tw-text-sm tw-text-gray-500">
                                                {{ log.user }} - {{ formatTime(log.timestamp) }}
                                            </p>
                                        </div>
                                        <span :class="[
                                            'tw-px-2 tw-py-1 tw-rounded-full tw-text-xs',
                                            `tw-bg-${getLogTypeColor(log.type)}-100`,
                                            `tw-text-${getLogTypeColor(log.type)}-700`
                                        ]">
                                            {{ getLogTypeText(log.type) }}
                                        </span>
                                    </div>
                                    <div v-if="log.details" class="tw-mt-2 tw-text-sm tw-text-gray-600">
                                        {{ log.details }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin'
})

import { ref, computed } from 'vue'

const searchQuery = ref('')
const filterType = ref('')
const startDate = ref('')
const endDate = ref('')

// Mock data
const logs = ref([
    {
        id: 1,
        type: 'order',
        message: 'Đơn hàng #123 đã được tạo',
        details: 'Khách hàng: Nguyễn Văn A, Tổng tiền: 15,000,000đ',
        user: 'nguyenvana@example.com',
        timestamp: '2024-03-15T10:30:00'
    },
    {
        id: 2,
        type: 'product',
        message: 'Sản phẩm mới được thêm vào',
        details: 'iPhone 15 Pro Max - SKU: IP15PM-256',
        user: 'admin@example.com',
        timestamp: '2024-03-15T09:15:00'
    },
    {
        id: 3,
        type: 'user',
        message: 'Người dùng mới đăng ký',
        details: 'Email: customer@example.com',
        user: 'System',
        timestamp: '2024-03-14T15:45:00'
    },
    {
        id: 4,
        type: 'system',
        message: 'Backup dữ liệu tự động',
        details: 'Backup thành công: 2.5GB',
        user: 'System',
        timestamp: '2024-03-14T00:00:00'
    }
])

const filteredLogs = computed(() => {
    return logs.value.filter(log => {
        const matchesSearch = log.message.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.details?.toLowerCase().includes(searchQuery.value.toLowerCase())
        const matchesType = !filterType.value || log.type === filterType.value
        const matchesDate = (!startDate.value || new Date(log.timestamp) >= new Date(startDate.value)) &&
            (!endDate.value || new Date(log.timestamp) <= new Date(endDate.value))
        return matchesSearch && matchesType && matchesDate
    })
})

const groupedLogs = computed(() => {
    const groups = {}
    filteredLogs.value.forEach(log => {
        const date = log.timestamp.split('T')[0]
        if (!groups[date]) {
            groups[date] = []
        }
        groups[date].push(log)
    })
    return groups
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('vi-VN', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const formatTime = (timestamp) => {
    return new Date(timestamp).toLocaleTimeString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getLogTypeIcon = (type) => {
    switch (type) {
        case 'order': return 'fas fa-shopping-cart'
        case 'product': return 'fas fa-box'
        case 'user': return 'fas fa-user'
        case 'system': return 'fas fa-cog'
        default: return 'fas fa-info-circle'
    }
}

const getLogTypeText = (type) => {
    switch (type) {
        case 'order': return 'Đơn hàng'
        case 'product': return 'Sản phẩm'
        case 'user': return 'Người dùng'
        case 'system': return 'Hệ thống'
        default: return type
    }
}

const getLogTypeColor = (type) => {
    switch (type) {
        case 'order': return 'blue'
        case 'product': return 'green'
        case 'user': return 'yellow'
        case 'system': return 'purple'
        default: return 'gray'
    }
}

const getLogTypeClass = (type) => {
    return `timeline-item-${type}`
}
</script>

<style scoped>
.logs-page {
    padding: 1.5rem;
}

.timeline {
    position: relative;
}

.timeline-group {
    margin-bottom: 2rem;
}

.timeline-date {
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 1rem;
}

.timeline-items {
    position: relative;
    padding-left: 2rem;
}

.timeline-items::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-icon {
    position: absolute;
    left: -2.5rem;
    width: 2rem;
    height: 2rem;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid;
}

.timeline-content {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-left: 0.5rem;
}

/* Timeline item types */
.timeline-item-order .timeline-icon {
    border-color: #3b82f6;
    color: #3b82f6;
}

.timeline-item-product .timeline-icon {
    border-color: #10b981;
    color: #10b981;
}

.timeline-item-user .timeline-icon {
    border-color: #f59e0b;
    color: #f59e0b;
}

.timeline-item-system .timeline-icon {
    border-color: #8b5cf6;
    color: #8b5cf6;
}
</style>