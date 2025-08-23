<template>
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-list-ol"></i>
            </div>
            <div class="stat-content">
                <h3>Tổng đơn hàng</h3>
                <p>{{ totalOrders }}</p>
            </div>
        </div>
        <div class="stat-card" v-for="(count, status) in statusCountMap" :key="status">
            <div class="stat-icon" :class="statusIconClass(status)">
                <i :class="statusIcon(status)"></i>
            </div>
            <div class="stat-content">
                <h3>{{ statusLabel(status) }}</h3>
                <p>{{ count }}</p>
            </div>
        </div>
    </div>
    <!-- Bảng debug trạng thái thực tế -->
    <!-- <div class="mt-8">
        <h4 class="font-semibold mb-2">Thống kê trạng thái thực tế (debug)</h4>
        <table class="w-full bg-white rounded shadow text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">Trạng thái</th>
                    <th class="px-4 py-2 text-left">Số lượng</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(count, status) in statusCountMap" :key="status">
                    <td class="px-4 py-2">{{ status }}</td>
                    <td class="px-4 py-2">{{ count }}</td>
                </tr>
            </tbody>
        </table>
    </div> -->
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    orders: {
        type: [Array, Object],
        required: true
    }
})

// Lấy danh sách đơn hàng từ props.orders (có thể là mảng hoặc object có .data)
const getOrderList = computed(() => {
    if (Array.isArray(props.orders)) {
        return props.orders
    } else if (props.orders && Array.isArray(props.orders.data)) {
        return props.orders.data
    }
    return []
})

const totalOrders = computed(() => getOrderList.value.length)

// Bảng đếm trạng thái thực tế (render thẻ)
const statusCountMap = computed(() => {
    const map = {}
    getOrderList.value.forEach(order => {
        const status = order?.status || 'unknown'
        map[status] = (map[status] || 0) + 1
    })
    return map
})

// Map nhãn trạng thái
const statusLabel = (status) => {
    switch (status) {
        case 'pending': return 'Chờ xác nhận'
        case 'processing': return 'Đang xử lý'
        case 'shipping': return 'Đang giao hàng'
        case 'completed': return 'Hoàn thành'
        case 'cancelled': return 'Đã hủy'
        case 'refunded': return 'Đã trả hàng'
        default: return status
    }
}
// Map icon trạng thái
const statusIcon = (status) => {
    switch (status) {
        case 'pending': return 'fas fa-clock'
        case 'processing': return 'fas fa-shipping-fast'
        case 'shipping': return 'fas fa-truck'
        case 'completed': return 'fas fa-check-circle'
        case 'cancelled': return 'fas fa-times-circle'
        case 'refunded': return 'fas fa-undo-alt'
        default: return 'fas fa-question-circle'
    }
}
// Map màu icon trạng thái
const statusIconClass = (status) => {
    switch (status) {
        case 'pending': return 'pending'
        case 'processing': return 'processing'
        case 'shipping': return 'shipping'
        case 'completed': return 'completed'
        case 'cancelled': return 'cancelled'
        case 'refunded': return 'returned'
        default: return 'unknown'
    }
}
</script>

<style scoped>
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-icon.total {
    background: #fef9c3;
    color: #b45309;
}

.stat-icon.pending {
    background: #fff7ed;
    color: #c2410c;
}

.stat-icon.processing {
    background: #eff6ff;
    color: #1d4ed8;
}

.stat-icon.shipping {
    background: #f0fdf4;
    color: #15803d;
}

.stat-icon.completed {
    background: #f0fdf4;
    color: #15803d;
}

.stat-icon.cancelled {
    background: #fef2f2;
    color: #dc2626;
}

.stat-icon.unknown {
    background: #f3f4f6;
    color: #6b7280;
}

.stat-content h3 {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.stat-content p {
    font-size: 1.5rem;
    font-weight: 600;
    color: #111827;
}

@media (max-width: 1280px) {
    .stats-cards {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .stats-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .stats-cards {
        grid-template-columns: 1fr;
    }
}
</style>